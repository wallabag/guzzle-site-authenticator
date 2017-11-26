<?php

namespace BD\GuzzleSiteAuthenticator\Plugin;

use BD\GuzzleSiteAuthenticator\Authenticator\Factory;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\Cookie;
use Http\Message\CookieJar;
use Http\Promise\FulfilledPromise;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AuthenticatorPlugin implements Plugin
{
    private $configBuilder;
    private $authenticatorFactory;
    private $logger;
    private $client;
    private $cookieJar;

    public function __construct(SiteConfigBuilder $configBuilder, Factory $authenticatorFactory, HttpMethodsClient $client = null, CookieJar $cookieJar = null, LoggerInterface $logger = null)
    {
        $this->configBuilder = $configBuilder;
        $this->authenticatorFactory = $authenticatorFactory;
        $this->client = $client ?: new HttpMethodsClient(HttpClientDiscovery::find(), MessageFactoryDiscovery::find());
        $this->cookieJar = $cookieJar ?: new CookieJar();
        $this->logger = $logger ?: new NullLogger();
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        $this->loginIfRequired($request);

        /** @var Promise $promise */
        $promise = $next($request);
        /** @var ResponseInterface $response */
        $response = $promise->wait();

        $result = $this->loginIfRequested($response);

        if ($result) {
            return $first($request);
        }

        return new FulfilledPromise($response);
    }

    /**
     * @param RequestInterface $request
     *
     * @return false|ResponseInterface
     */
    private function loginIfRequired(RequestInterface $request)
    {
        $config = $this->configBuilder->buildForHost($request->getUri()->getAuthority());
        if ($config && $config->requiresLogin()) {
            $host = $config->getHost();
            $this->logger->debug('AuthenticatorPlugin: require login to host', ['host' => $host]);
            $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);
            if (!$this->isLoggedIn($request->getUri()->getHost())) {
                $this->logger->debug('AuthenticatorPlugin: login to host', ['host' => $host]);
                return $authenticator->login($this->client);
            }
        }

        return false;
    }

    public function loginIfRequested(ResponseInterface $response)
    {
        $config = $this->configBuilder->buildForHost(current($response->getHeader('Host')));
        if ($config && $config->requiresLogin()) {
            $authenticator = $this->authenticatorFactory->buildFromSiteConfig($config);
            if ($authenticator->isLoginRequired($response)) {
                $this->logger->debug('AuthenticatorPlugin: login required to host', ['host' => $config->getHost()]);
                return $authenticator->login($this->client);
            }

        }

        return false;
    }

    public function isLoggedIn($host)
    {
        /** @var Cookie $cookie */
        foreach ($this->cookieJar as $cookie) {
            if ($cookie->getDomain() === $host) {
                $this->logger->debug('AuthenticatorPlugin: already logged', ['host' => $host]);
                return true;
            }
        }

        return false;
    }
}
