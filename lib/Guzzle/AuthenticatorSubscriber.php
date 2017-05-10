<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\GuzzleSiteAuthenticator\Guzzle;

use BD\GuzzleSiteAuthenticator\Authenticator\Factory;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\RequestInterface;

class AuthenticatorSubscriber implements SubscriberInterface
{
    // avoid loop when login failed which can just be a bad login/password
    // after 2 attempts, we skip the login
    const MAX_RETRIES = 2;
    private static $retries = 0;

    /** @var SiteConfigBuilder */
    private $configBuilder;

    /** @var Factory */
    private $authenticatorFactory;

    /**
     * AuthenticatorSubscriber constructor.
     *
     * @param SiteConfigBuilder $configBuilder
     * @param Factory           $authenticatorFactory
     */
    public function __construct(SiteConfigBuilder $configBuilder, Factory $authenticatorFactory)
    {
        $this->configBuilder = $configBuilder;
        $this->authenticatorFactory = $authenticatorFactory;
    }

    public function getEvents()
    {
        return [
            'before' => ['loginIfRequired'],
            'complete' => ['loginIfRequested'],
        ];
    }

    public function loginIfRequired(BeforeEvent $event)
    {
        $config = $this->buildSiteConfig($event->getRequest());
        if ($config === false || !$config->requiresLogin()) {
            return;
        }

        $client = $event->getClient();
        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);

        if (!$authenticator->isLoggedIn($client)) {
            $emitter = $client->getEmitter();
            $emitter->detach($this);
            $authenticator->login($client);
            $emitter->attach($this);
        }
    }

    public function loginIfRequested(CompleteEvent $event)
    {
        $config = $this->buildSiteConfig($event->getRequest());
        if ($config === false || !$config->requiresLogin()) {
            return;
        }

        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);
        $isLoginRequired = $authenticator->isLoginRequired($event->getResponse()->getBody());

        if ($isLoginRequired && self::$retries < self::MAX_RETRIES) {
            $client = $event->getClient();

            $emitter = $client->getEmitter();
            $emitter->detach($this);
            $authenticator->login($client);
            $emitter->attach($this);

            $event->retry();

            ++self::$retries;
        }
    }

    /**
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig|false
     */
    private function buildSiteConfig(RequestInterface $request)
    {
        return $this->configBuilder->buildForHost($request->getHost());
    }
}
