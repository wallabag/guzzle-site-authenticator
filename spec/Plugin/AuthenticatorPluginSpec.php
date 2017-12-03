<?php

namespace spec\BD\GuzzleSiteAuthenticator\Plugin;

use BD\GuzzleSiteAuthenticator\Authenticator\Authenticator;
use BD\GuzzleSiteAuthenticator\Authenticator\Factory;
use BD\GuzzleSiteAuthenticator\Plugin\AuthenticatorPlugin;
use BD\GuzzleSiteAuthenticator\SiteConfig\NullSiteConfig;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\Common\HttpMethodsClient;
use Http\Message\CookieJar;
use Http\Promise\FulfilledPromise;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Http\Client\Common\Plugin;

class AuthenticatorPluginSpec extends ObjectBehavior
{
    function let(
        SiteConfigBuilder $siteConfigBuilder,
        Factory $authenticatorFactory,
        HttpMethodsClient $httpClient
    ) {
        $this->beConstructedWith($siteConfigBuilder, $authenticatorFactory, $httpClient, new CookieJar);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AuthenticatorPlugin::class);
    }

    function it_is_a_plugin()
    {
        $this->shouldImplement(Plugin::class);
    }

    public function it_ignores_sites_that_dont_require_login(
        SiteConfigBuilder $siteConfigBuilder,
        HttpMethodsClient $httpMethodsClient,
        Factory $authenticatorFactory,
        Authenticator $authenticator
    ) {
        $siteConfigBuilder->buildForHost('example.com')->willReturn(new SiteConfig([
            'host' => 'example.com',
            'requiresLogin' => false
        ]));
        $authenticator->login(Argument::any())->shouldNotBeCalled();
        $authenticatorFactory->buildFromSiteConfig()->willReturn($authenticator);

        $this->beConstructedWith($siteConfigBuilder, $authenticatorFactory, $httpMethodsClient, new CookieJar);

        $response = new Response(200, ['host' => 'example.com']);
        $next = function () use ($response) {
            return new FulfilledPromise($response);
        };
        $this->handleRequest(new Request('GET', 'http://example.com'), $next, function () {});
    }

    public function it_logs_in_before_requests_to_sites_that_require_login(
        SiteConfigBuilder $siteConfigBuilder,
        HttpMethodsClient $httpMethodsClient,
        Factory $authenticatorFactory,
        Authenticator $authenticator
    ) {
        $siteConfig = new SiteConfig([
            'host' => 'example.com',
            'requiresLogin' => true,
            'loginUri' => 'http://example.com/login',
            'usernameField' => '_username',
            'passwordField' => '_password',

        ]);
        $siteConfigBuilder->buildForHost('example.com')->willReturn($siteConfig);
        $authenticatorFactory->buildFromSiteConfig($siteConfig)->willReturn($authenticator);
        $authenticator->isLoginRequired(Argument::any())->willReturn(true);

        $authenticator->login(Argument::any())->shouldBeCalled();

        $this->beConstructedWith($siteConfigBuilder, $authenticatorFactory, $httpMethodsClient, new CookieJar);

        $response = new Response(200, ['host' => 'example.com']);
        $next = function () use ($response) {
            return new FulfilledPromise($response);
        };
        $this->handleRequest(new Request('GET', 'http://example.com'), $next, function () {});
    }

    public function it_logs_in_after_responses_that_required_login_and_retries_the_request(
        SiteConfigBuilder $siteConfigBuilder,
        HttpMethodsClient $httpMethodsClient,
        Factory $authenticatorFactory,
        Authenticator $authenticator
    ) {
        $siteConfig = new SiteConfig([
            'host' => 'example.com',
            'requiresLogin' => true,
            'loginUri' => 'http://example.com/login',
            'usernameField' => '_username',
            'passwordField' => '_password',

        ]);
        $siteConfigBuilder->buildForHost('example.com')->willReturn($siteConfig);
        $authenticatorFactory->buildFromSiteConfig($siteConfig)->willReturn($authenticator);
        $authenticator->isLoginRequired(Argument::any())->willReturn(true);

        $authenticator->login(Argument::any())->shouldBeCalledTimes(2);

        $this->beConstructedWith($siteConfigBuilder, $authenticatorFactory, $httpMethodsClient, new CookieJar);

        $response = new Response(200, ['host' => 'example.com']);
        $next = function () use ($response) {
            return new FulfilledPromise($response);
        };
        $this->handleRequest(new Request('GET', 'http://example.com'), $next, function () {});
    }

    public function it_ignores_requests_to_sites_without_config(
        SiteConfigBuilder $siteConfigBuilder,
        HttpMethodsClient $httpMethodsClient,
        Factory $authenticatorFactory,
        Authenticator $authenticator
    ) {
        $siteConfigBuilder->buildForHost(Argument::any())->willReturn(new NullSiteConfig());

        $authenticator->login(Argument::any())->shouldNotBeCalled();

        $this->beConstructedWith($siteConfigBuilder, $authenticatorFactory, $httpMethodsClient, new CookieJar);

        $response = new Response(200, ['host' => 'example.com']);
        $next = function () use ($response) {
            return new FulfilledPromise($response);
        };
        $this->handleRequest(new Request('GET', 'http://example.com'), $next, function () {});
    }
}
