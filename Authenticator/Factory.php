<?php

namespace BD\GuzzleSiteAuthenticatorBundle\Authenticator;

use Graby\SiteConfig\SiteConfig;
use GuzzleHttp\Client;

/**
 * Builds an Authenticator based on a SiteConfig.
 */
class Factory
{
    /**
     * @var \BD\GuzzleSiteAuthenticatorBundle\Authenticator\CredentialsBag
     */
    private $credentialsBag;

    /**
     * Factory constructor.
     *
     * @param CredentialsBag $credentialsBag
     */
    public function __construct(CredentialsBag $credentialsBag = null)
    {
        $this->credentialsBag = $credentialsBag;
    }

    /**
     * @return Authenticator
     * @throw \OutOfRangeException if there are no credentials for this host
     */
    public function buildFromSiteConfig($host, SiteConfig $siteConfig)
    {
        $authenticator = new LoginFormAuthenticator(
            $host,
            [
                'username_field' => $siteConfig->login_username_field,
                'password_field' => $siteConfig->login_password_field,
                'not_logged_in_xpath' => $siteConfig->not_logged_in_xpath,
                'uri' => $siteConfig->login_uri,
                'extra_fields' => $siteConfig->login_extra_fields
            ],
            $this->credentialsBag->getCredentialsForHost($host)
        );

        return $authenticator;
    }
}
