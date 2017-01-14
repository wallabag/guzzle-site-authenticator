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
use GuzzleHttp\Message\Response;
use GuzzleHttp\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FixupMondeDiplomatiqueUriSubscriberSpec extends ObjectBehavior
{
    function let(CompleteEvent $event, ResponseInterface $response)
    {
        $event->getResponse()->willReturn($response);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('BD\GuzzleSiteAuthenticator\Guzzle\FixupMondeDiplomatiqueUriSubscriber');
    }

    function it_registers_the_complete_request_event()
    {
        $this->getEvents()->shouldHaveKey('complete');
    }

    function it_ignores_responses_without_a_location_header(CompleteEvent $event, ResponseInterface $response)
    {
        $this->fixUri($event);
    }

    function it_encodes_the_unencoded_retour_parameter(CompleteEvent $event, ResponseInterface $response)
    {
        $response->hasHeader('Location')->willReturn(true);
        $response->getHeader('Location')->willReturn('http://example.com/?foo=bar&retour=http://example.com');

        $response->setHeader('Location', 'http://example.com/?foo=bar&retour%3Dhttp%3A%2F%2Fexample.com')->shouldBeCalled();

        $this->fixUri($event);
    }
}
