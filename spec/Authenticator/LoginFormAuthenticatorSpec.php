<?php

namespace spec\BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @method login
 */
class LoginFormAuthenticatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'example.com',
            [
                'uri' => 'http://example.com/login',
                'username_field' => 'username',
                'password_field' => 'password',
                'extra_fields' => [
                    'action' => 'login',
                    'foo' => 'bar'
                ],
            ],
            [
                'username' => 'johndoe',
                'password' => 'unkn0wn'
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('BD\GuzzleSiteAuthenticator\Authenticator\LoginFormAuthenticator');
    }

    function it_posts_a_login_request(ClientInterface $guzzle)
    {
        $guzzle->post(
            'http://example.com/login',
            [
                'body' => [
                    'username' => 'johndoe',
                    'password' => 'unkn0wn',
                    'action' => 'login',
                    'foo' => 'bar'
                ],
                'verify' => false,
                'allow_redirects' => true
            ]
        )->shouldBeCalled();

        $guzzle->getDefaultOption('cookies')->willReturn(new CookieJar(false, new SetCookie()));

        $this->login($guzzle);
    }
}
