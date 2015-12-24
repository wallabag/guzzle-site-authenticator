<?php

namespace BD\GuzzleSiteAuthenticatorBundle\Authenticator;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use Exception;

class LoginFormAuthenticator implements Authenticator
{
    /** @var array Array with 'username' and 'password' keys */
    private $credentials;

    /** @var \GuzzleHttp\Client */
    protected $guzzle;

    /** @var array */
    private $formOptions;

    /** @var string */
    private $host;

    public function __construct($host, array $formOptions = [], array $credentials)
    {
        // @todo OptionResolver
        $this->formOptions = $formOptions;
        $this->credentials = $credentials;
        $this->host = $host;
    }

    public function login(ClientInterface $guzzle)
    {
        $postFields = [
            $this->formOptions['username_field'] => $this->credentials['username'],
            $this->formOptions['password_field'] => $this->credentials['password'],
        ] + $this->formOptions['extra_fields'];

        $guzzle->post(
            $this->formOptions['uri'],
            ['body' => $postFields, 'allow_redirects' => true, 'verify' => false]
        );

        if (!$this->isLoggedIn($guzzle)) {
            throw new Exception("Login to {$this->host} failed");
        }

        return $this;
    }

    public function isLoggedIn(ClientInterface $guzzle)
    {
        if (($cookieJar = $guzzle->getDefaultOption('cookies')) instanceof CookieJar) {
            /** @var \GuzzleHttp\Cookie\SetCookie $cookie */
            foreach ($cookieJar as $cookie) {
                // check required cookies
                if ($cookie->getDomain() == $this->host) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks from the HTML of a page if authentication is requested by a grabbed page.
     *
     * @param string $html
     *
     * @return bool
     */
    public function isLoginRequired($html)
    {
        $useInternalErrors = libxml_use_internal_errors(true);

        // need to check for the login dom element ($options['not_logged_in_xpath']) in the HTML
        $doc = new \DOMDocument();
        $doc->loadHTML($html);

        $xpath = new \DOMXPath($doc);
        $result = ($xpath->evaluate($this->formOptions['not_logged_in_xpath'])->length > 0);

        libxml_use_internal_errors($useInternalErrors);

        return $result;
    }
}
