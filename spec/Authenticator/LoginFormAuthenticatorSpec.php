<?php

namespace spec\BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\Authenticator\LoginFormAuthenticator;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use GuzzleHttp\Psr7\Response;
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

    function it_should_detect_if_login_is_required_when_the_xpath_match_one_element()
    {
        $siteConfig = new SiteConfig([
            'notLoggedInXpath' => '//button[@class="sign-in"]',
        ]);
        $this->beConstructedWith($siteConfig);

        $response = new Response(200, [], <<<HTML
<html>
<body>
    <button class="sign-in">Sign in</button>
</body>
</html> 
HTML
);
        $this->isLoginRequired($response)->shouldBe(true);
    }

    function it_should_detect_if_login_is_not_required_when_the_xpath_does_not_match_one_element()
    {
        $siteConfig = new SiteConfig([
            'notLoggedInXpath' => '//button[@class="sign-in"]',
        ]);
        $this->beConstructedWith($siteConfig);

        $response = new Response(200, [], <<<HTML
<html>
<body>
    <button class="sign-out">Logout</button>
</body>
</html> 
HTML
        );
        $this->isLoginRequired($response)->shouldBe(false);
    }

    function it_should_detect_if_login_is_required_when_the_xpath_is_a_boolean_expression()
    {
        $siteConfig = new SiteConfig([
            'notLoggedInXpath' => 'not(boolean(//button[@class="sign-out"]))',
        ]);
        $this->beConstructedWith($siteConfig);

        $response = new Response(200, [], <<<HTML
<html>
<body>
    <button class="sign-out">Logout</button>
</body>
</html> 
HTML
        );
        $this->isLoginRequired($response)->shouldBe(false);
    }
}
