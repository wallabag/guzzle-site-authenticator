<?php

namespace spec\BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use PhpSpec\ObjectBehavior;

/**
 * @method login
 */
class LoginFormAuthenticatorSpec extends ObjectBehavior
{
    public function let($siteConfig)
    {
        $siteConfig = new SiteConfig([
        'host' => 'example.com',
            'loginUri' => 'http://example.com/login',
            'usernameField' => 'username',
            'passwordField' => 'password',
            'extraFields' => [
                'action=login',
                'foo=bar',
            ],
            'username' => 'johndoe',
            'password' => 'unkn0wn',
        ]);
        $this->beConstructedWith($siteConfig);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('BD\GuzzleSiteAuthenticator\Authenticator\LoginFormAuthenticator');
    }

    public function it_posts_a_login_request(ClientInterface $guzzle)
    {
        $guzzle->post(
            'http://example.com/login',
            [
                'body' => [
                    'username' => 'johndoe',
                    'password' => 'unkn0wn',
                    'action' => 'login',
                    'foo' => 'bar',
                ],
                'verify' => false,
                'allow_redirects' => true,
            ]
        )->shouldBeCalled();

        $guzzle->getDefaultOption('cookies')->willReturn(new CookieJar(false, new SetCookie()));

        $this->login($guzzle);
    }
}
