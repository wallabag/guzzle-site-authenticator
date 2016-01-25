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
use OutOfRangeException;
use Psr\Log\LoggerAwareTrait;

class AuthenticatorSubscriber implements SubscriberInterface
{
    use LoggerAwareTrait;

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

    public function loginIfRequired(BeforeEvent $event)
    {
        if (($config = $this->buildSiteConfig($event->getRequest())) === false) {
            return;
        }

        if (!$config->requiresLogin()) {
            return;
        }

        $this->logDebug("Site config for " . $config->getHost() . " requires login");

        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);
        $client = $event->getClient();
        if (!$authenticator->isLoggedIn($client)) {
            $this->logDebug("No existing login data for " . $config->getHost() . " found");
            $emitter = $client->getEmitter();
            $emitter->detach($this);

            $this->logInfo("Logging in to " . $config->getHost());
            $authenticator->login($client);

            $emitter->attach($this);
        } else {
            $this->logDebug("Found existing login data for " . $config->getHost());
        }
    }

    public function loginIfRequested(CompleteEvent $event)
    {
        if (($config = $this->buildSiteConfig($event->getRequest())) === false) {
            return;
        }

        if (!$config->requiresLogin()) {
            return;
        }

        $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);

        if ($authenticator->isLoginRequired($event->getResponse()->getBody())) {
            $this->logDebug("Response for " . $event->getRequest()->getUrl() . " requires login");
            $client = $event->getClient();

            $emitter = $client->getEmitter();
            $emitter->detach($this);
            $this->logInfo("Logging in to " . $config->getHost());
            $authenticator->login($client);
            $emitter->attach($this);

            $event->retry();
        }
    }

    /**
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig|false
     */
    private function buildSiteConfig(RequestInterface $request)
    {
        return $this->configBuilder->buildForHost($request->getHost());
    }

    private function logDebug($message, array $context = [])
    {
        if (!isset($this->logger)) {
            return;
        }

        $this->logger->debug($message, $context);
    }

    private function logInfo($message, array $context = [])
    {
        if (!isset($this->logger)) {
            return;
        }

        $this->logger->info($message, $context);
    }
}
