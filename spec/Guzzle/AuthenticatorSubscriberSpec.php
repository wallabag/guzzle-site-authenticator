<?php

namespace spec\BD\GuzzleSiteAuthenticator\Guzzle;

use BD\GuzzleSiteAuthenticator\Authenticator\Authenticator;
use BD\GuzzleSiteAuthenticator\Authenticator\Factory;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\Emitter;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuthenticatorSubscriberSpec extends ObjectBehavior
{
    public function let(
        SiteConfigBuilder $siteConfigBuilder,
        SiteConfig $siteConfig,
        ClientInterface $guzzle,
        Emitter $emitter,
        BeforeEvent $beforeEvent,
        CompleteEvent $completeEvent,
        RequestInterface $request,
        ResponseInterface $response)
    {
        $siteConfig->getHost()->willReturn('example.com');
        $siteConfigBuilder->buildForHost('example.com')->willReturn($siteConfig);

        $guzzle->getEmitter()->willReturn($emitter);

        $request->getHost()->willReturn('example.com');
        $beforeEvent->getRequest()->willReturn($request);
        $beforeEvent->getClient()->willReturn($guzzle);

        $response->getBody()->willReturn('<html></html>');
        $completeEvent->getResponse()->willReturn($response);
        $completeEvent->getRequest()->willReturn($request);
        $completeEvent->getClient()->willReturn($guzzle);

        $this->beConstructedWith($siteConfigBuilder, $authenticatorFactory);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('BD\GuzzleSiteAuthenticator\Guzzle\AuthenticatorSubscriber');
    }

    public function it_registers_the_before_request_event()
    {
        $this->getEvents()->shouldHaveKey('before');
    }

    public function it_registers_the_complete_request_event()
    {
        $this->getEvents()->shouldHaveKey('complete');
    }

    public function it_ignores_sites_that_dont_require_login(
        Factory $authenticatorFactory,
        SiteConfig $siteConfig,
        BeforeEvent $beforeEvent
    ) {
        $siteConfig->requiresLogin()->willReturn(false);
        $authenticatorFactory->buildFromSiteConfig()->shouldNotBeCalled();

        $this->loginIfRequired($beforeEvent);
    }

    public function it_logs_in_before_requests_to_sites_that_require_login(
        SiteConfig $siteConfig,
        Factory $authenticatorFactory,
        BeforeEvent $beforeEvent,
        Authenticator $authenticator,
        ClientInterface $guzzle
    ) {
        $authenticatorFactory->buildFromSiteConfig($siteConfig)->willReturn($authenticator);
        $siteConfig->requiresLogin()->willReturn(true);
        $authenticator->isLoggedIn($guzzle)->willReturn(false);
        $authenticator->login($guzzle)->shouldBeCalled();

        $this->loginIfRequired($beforeEvent);
    }

    public function it_logs_in_after_responses_that_required_login_and_retries_the_request(
        SiteConfig $siteConfig,
        Factory $authenticatorFactory,
        CompleteEvent $completeEvent,
        Authenticator $authenticator,
        ClientInterface $guzzle
    ) {
        $siteConfig->requiresLogin()->willReturn(true);

        $authenticatorFactory->buildFromSiteConfig($siteConfig)->willReturn($authenticator);
        $authenticator->isLoginRequired(Argument::type('string'))->willReturn(true);
        $authenticator->login($guzzle)->shouldBeCalled();
        $completeEvent->retry()->shouldBeCalled();

        $this->loginIfRequested($completeEvent);
    }

    public function it_ignores_requests_to_sites_without_config(
        BeforeEvent $beforeEvent,
        CompleteEvent $completeEvent,
        SiteConfig $siteConfig
    ) {
        $siteConfig->requiresLogin()->willReturn(false);

        $this->loginIfRequired($beforeEvent)->shouldReturn(null);
        $this->loginIfRequested($completeEvent)->shouldReturn(null);
    }
}
