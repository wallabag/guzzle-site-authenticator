<?php

namespace Tests\BD\GuzzleSiteAuthenticator\Guzzle;

use BD\GuzzleSiteAuthenticator\Guzzle\AuthenticatorSubscriber;
use BD\GuzzleSiteAuthenticator\SiteConfig\ArraySiteConfigBuilder;
use BD\GuzzleSiteAuthenticator\Authenticator\Factory;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit\Framework\TestCase;

class AuthenticatorSubscriberTest extends TestCase
{
    public function testGetEvents()
    {
        $subscriber = new AuthenticatorSubscriber(
            new ArraySiteConfigBuilder(),
            new Factory()
        );
        $events = $subscriber->getEvents();

        $this->assertArrayHasKey('before', $events);
        $this->assertArrayHasKey('complete', $events);
        $this->assertSame('loginIfRequired', $events['before'][0]);
        $this->assertSame('loginIfRequested', $events['complete'][0]);
    }

    public function testLoginIfRequired()
    {
        $builder = new ArraySiteConfigBuilder(['example.com' => []]);
        $subscriber = new AuthenticatorSubscriber($builder, new Factory());

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

        $subscriber->loginIfRequired($event);
    }
}
