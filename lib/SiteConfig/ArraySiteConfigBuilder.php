<?php

namespace BD\GuzzleSiteAuthenticator\SiteConfig;

class ArraySiteConfigBuilder implements SiteConfigBuilder
{
    /**
     * Map of hostname => SiteConfig.
     */
    private $configs = [];

    public function __construct(array $hostConfigMap = [])
    {
        foreach ($hostConfigMap as $host => $hostConfig) {
            $hostConfig['host'] = $host;
            $this->configs[$host] = new SiteConfig($hostConfig);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForHost($host)
    {
        $host = strtolower($host);

        if (substr($host, 0, 4) === 'www.') {
            $host = substr($host, 4);
        }

        if (isset($this->configs[$host])) {
            return $this->configs[$host];
        }

        // TODO: NullSiteConfig
        return false;
    }
}
