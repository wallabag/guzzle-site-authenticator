<?php

namespace BD\GuzzleSiteAuthenticator\SiteConfig;

class NullSiteConfig extends SiteConfig
{
    public function __construct()
    {
        $this->requiresLogin = false;
    }
}
