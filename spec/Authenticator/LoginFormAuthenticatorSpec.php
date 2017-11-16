<?php

namespace spec\BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\Authenticator\LoginFormAuthenticator;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use Http\Client\Common\HttpMethodsClient;
use PhpSpec\ObjectBehavior;

/**
 * @method login
 */
class LoginFormAuthenticatorSpec extends ObjectBehavior
{
    function let()
    {
        $siteConfig = new SiteConfig([
            'host' => 'example.com',
            'loginUri' => 'http://example.com/login',
            'usernameField' => 'username',
            'passwordField' => 'password',
            'extraFields' => [
                'action' => 'login',
                'foo' => 'bar',
            ],
            'username' => 'johndoe',
            'password' => 'unkn0wn',
        ]);
        $this->beConstructedWith($siteConfig);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LoginFormAuthenticator::class);
    }

    function it_posts_a_login_request(HttpMethodsClient $httpClient)
    {
        $httpClient->post(
            'http://example.com/login',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            'username=johndoe&password=unkn0wn&action=login&foo=bar'
        )->shouldBeCalled();

        $this->login($httpClient);
    }
}
