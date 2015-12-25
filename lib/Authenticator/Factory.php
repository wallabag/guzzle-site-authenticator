<?php

namespace BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;

/**
 * Builds an Authenticator based on a SiteConfig.
 */
class Factory
{
    /**
     * @var \BD\GuzzleSiteAuthenticator\Authenticator\CredentialsBag
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
     * @param $host
     * @param \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig $siteConfig
     *
     * @return \BD\GuzzleSiteAuthenticator\Authenticator\Authenticator
     * @throw \OutOfRangeException if there are no credentials for this host
     */
    public function buildFromSiteConfig($host, SiteConfig $siteConfig)
    {
        $authenticator = new LoginFormAuthenticator(
            $host,
            [
                'username_field' => $siteConfig->getUsernameField(),
                'password_field' => $siteConfig->getPasswordField(),
                'not_logged_in_xpath' => $siteConfig->getNotLoggedInXpath(),
                'uri' => $siteConfig->getLoginUri(),
                'extra_fields' => $siteConfig->getExtraFields()
            ],
            $this->credentialsBag->getCredentialsForHost($host)
        );

        return $authenticator;
    }
}
