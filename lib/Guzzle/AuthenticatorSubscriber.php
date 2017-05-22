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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AuthenticatorSubscriber implements SubscriberInterface, LoggerAwareInterface
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
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
            $this->logger->debug('loginIfRequired> will not require login');

            return;
        }

        $client = $event->getClient();
        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);

        if (!$authenticator->isLoggedIn($client)) {
            $this->logger->debug('loginIfRequired> user is not logged in, attach authenticator');

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
            $this->logger->debug('loginIfRequested> will not require login');

            return;
        }

        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);
        $isLoginRequired = $authenticator->isLoginRequired($event->getResponse()->getBody());

        $this->logger->debug('loginIfRequested> retry #' . self::$retries . ' with login ' . ($isLoginRequired ?: 'not ') . 'required');

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
