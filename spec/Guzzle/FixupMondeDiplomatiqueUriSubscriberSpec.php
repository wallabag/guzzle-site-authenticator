<?php

namespace spec\BD\GuzzleSiteAuthenticator\Guzzle;

use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;

class FixupMondeDiplomatiqueUriSubscriberSpec extends ObjectBehavior
{
    public function let(CompleteEvent $event, ResponseInterface $response)
    {
        $event->getResponse()->willReturn($response);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('BD\GuzzleSiteAuthenticator\Guzzle\FixupMondeDiplomatiqueUriSubscriber');
    }

    public function it_registers_the_complete_request_event()
    {
        $this->getEvents()->shouldHaveKey('complete');
    }

    public function it_ignores_responses_without_a_location_header(CompleteEvent $event, ResponseInterface $response)
    {
        $this->fixUri($event);
    }

    public function it_encodes_the_unencoded_retour_parameter(CompleteEvent $event, ResponseInterface $response)
    {
        $response->hasHeader('Location')->willReturn(true);
        $response->getHeader('Location')->willReturn('http://example.com/?foo=bar&retour=http://example.com');

        $response->setHeader('Location', 'http://example.com/?foo=bar&retour%3Dhttp%3A%2F%2Fexample.com')->shouldBeCalled();

        $this->fixUri($event);
    }
}
