<?php

namespace BD\GuzzleSiteAuthenticatorBundle\Authenticator;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;

class ConfigurableAuthenticator extends AbstractAuthenticator
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function getUri()
    {
        return $this->options['uri'];
    }


    public function getUsernameFieldName()
    {
        return $this->options['field_username'];
    }

    public function getPasswordFieldName()
    {
        return $this->options['field_password'];
    }

    public function getExtraFormFields()
    {
        return $this->options['extra_fields'];
    }

    public function verifyCookies(CookieJar $cookieJar)
    {
        /** @var SetCookie $cookie */
        foreach ($cookieJar as $cookie) {
            if ($cookie->getDomain() === '.mediapart.fr' && $cookie->getName() == 'MPSESSID') {
                return true;
            }
        }

        throw new AuthenticatorException($this->getUri());
    }

    public function requiresAuth($html)
    {
    }
}
