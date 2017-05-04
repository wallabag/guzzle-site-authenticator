<?php

namespace BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;

class LoginFormAuthenticator implements Authenticator
{
    /** @var \GuzzleHttp\Client */
    protected $guzzle;

    /** @var SiteConfig */
    private $siteConfig;

    public function __construct(SiteConfig $siteConfig)
    {
        // @todo OptionResolver
        $this->siteConfig = $siteConfig;
    }

    public function login(ClientInterface $guzzle)
    {
        $postFields = [
            $this->siteConfig->getUsernameField() => $this->siteConfig->getUsername(),
            $this->siteConfig->getPasswordField() => $this->siteConfig->getPassword(),
        ] + $this->siteConfig->getExtraFields();

        $guzzle->post(
            $this->siteConfig->getLoginUri(),
            ['body' => $postFields, 'allow_redirects' => true, 'verify' => false]
        );

        return $this;
    }

    public function isLoggedIn(ClientInterface $guzzle)
    {
        if (($cookieJar = $guzzle->getDefaultOption('cookies')) instanceof CookieJar) {
            /** @var \GuzzleHttp\Cookie\SetCookie $cookie */
            foreach ($cookieJar as $cookie) {
                // check required cookies
                if ($cookie->getDomain() === $this->siteConfig->getHost()) {
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
        $result = ($xpath->evaluate($this->siteConfig->getNotLoggedInXpath())->length > 0);

        libxml_use_internal_errors($useInternalErrors);

        return $result;
    }
}
