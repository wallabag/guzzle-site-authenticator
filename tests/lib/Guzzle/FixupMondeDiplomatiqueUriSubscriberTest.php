<?php

namespace Tests\BD\GuzzleSiteAuthenticator\Guzzle;

use BD\GuzzleSiteAuthenticator\Guzzle\FixupMondeDiplomatiqueUriSubscriber;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit\Framework\TestCase;

class FixupMondeDiplomatiqueUriSubscriberTest extends TestCase
{
    public function testGetEvents()
    {
        $subscriber = new FixupMondeDiplomatiqueUriSubscriber();
        $events = $subscriber->getEvents();

        $this->assertArrayHasKey('complete', $events);
        $this->assertCount(2, $events['complete'][0]);
    }

    public function testGetEventsWithoutHeaderLocation()
    {
        $response = new Response(
            200,
            [
                'content-type' => 'text/html',
            ],
            Stream::factory('<html></html>')
        );

        $event = $this->getMockBuilder('GuzzleHttp\Event\CompleteEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $subscriber = new FixupMondeDiplomatiqueUriSubscriber();
        $subscriber->fixUri($event);

        $this->assertFalse($response->hasHeader('Location'));
    }

    public function testGetEventsWithNotMachingHeaderLocation()
    {
        $response = new Response(
            200,
            [
                'content-type' => 'text/html',
                'Location' => 'http://example.com',
            ],
            Stream::factory('<html></html>')
        );

        $event = $this->getMockBuilder('GuzzleHttp\Event\CompleteEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $subscriber = new FixupMondeDiplomatiqueUriSubscriber();
        $subscriber->fixUri($event);

        $this->assertSame('http://example.com', $response->getHeader('Location'));
    }

    public function testGetEventsWithMachingHeaderLocation()
    {
        $response = new Response(
            200,
            [
                'content-type' => 'text/html',
                'Location' => 'http://example.com/?foo=bar&retour=http://example.com',
            ],
            Stream::factory('<html></html>')
        );

        $event = $this->getMockBuilder('GuzzleHttp\Event\CompleteEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getResponse')
            ->willReturn($response);

        $subscriber = new FixupMondeDiplomatiqueUriSubscriber();
        $subscriber->fixUri($event);

        $this->assertSame('http://example.com/?foo=bar&retour%3Dhttp%3A%2F%2Fexample.com', $response->getHeader('Location'));
    }
}
