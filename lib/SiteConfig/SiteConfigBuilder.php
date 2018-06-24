<?php

namespace BD\GuzzleSiteAuthenticator\SiteConfig;

interface SiteConfigBuilder
{
    /**
     * Builds the SiteConfig for a host.
     * If there is not a specific SiteConfig, return a NullSiteConfig.
     *
     * @param string $host The "www." prefix is ignored.
     *
     * @return SiteConfig
     */
    public function buildForHost($host);
}
