<?php

namespace BD\GuzzleSiteAuthenticator\Authenticator;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;

/**
 * Builds an Authenticator based on a SiteConfig.
 */
class Factory
{
    /**
     * @return Authenticator
     *
     * @throw \OutOfRangeException if there are no credentials for this host
     */
    public function buildFromSiteConfig(SiteConfig $siteConfig)
    {
        return new LoginFormAuthenticator($siteConfig);
    }
}
