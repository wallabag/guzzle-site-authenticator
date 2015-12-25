<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\Guzzle;

use BD\GuzzleSiteAuthenticator\Authenticator\Factory;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Message\RequestInterface;

class AuthenticatorSubscriber implements SubscriberInterface
{
    /**
     * @var \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder
     */
    private $configBuilder;

    /** @var \BD\GuzzleSiteAuthenticator\Authenticator\Factory */
    private $authenticatorFactory;

    /**
     * AuthenticatorSubscriber constructor.
     *
     * @param \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder $configBuilder
     * @param \BD\GuzzleSiteAuthenticator\Authenticator\Factory $authenticatorFactory
     */
    public function __construct(SiteConfigBuilder $configBuilder, Factory $authenticatorFactory)
    {
        $this->configBuilder = $configBuilder;
        $this->authenticatorFactory = $authenticatorFactory;
    }

    public function getEvents()
    {
        return [
            'before'   => ['loginIfRequired'],
            'complete' => ['loginIfRequested'],
        ];
    }

    public function loginIfRequired(BeforeEvent $event, $name)
    {
        $request = $event->getRequest();
        $host = $request->getHost();
        $client = $event->getClient();

        $config = $this->buildSiteConfig($event->getRequest());
        if (!$config->requiresLogin()) {
            return;
        }

        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($host, $config);
        if (!$authenticator->isLoggedIn($client)) {
            $emitter = $client->getEmitter();
            $emitter->detach($this);
            $authenticator->login($client);
            $emitter->attach($this);
        }
    }

    public function loginIfRequested(CompleteEvent $event, $name)
    {
        $html = $event->getResponse()->getBody();
        $config = $this->buildSiteConfig($event->getRequest());

        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($event->getRequest()->getHost(), $config);

        if ($authenticator->isLoginRequired($html)) {
            $client = $event->getClient();

            $emitter = $client->getEmitter();
            $emitter->detach($this);
            $authenticator->login($client);
            $emitter->attach($this);

            $event->retry();
        }
    }

    private function buildSiteConfig(RequestInterface $request)
    {
        return $this->configBuilder->buildForHost($request->getHost());
    }
}
