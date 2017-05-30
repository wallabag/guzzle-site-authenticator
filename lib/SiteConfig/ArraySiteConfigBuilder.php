<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\GuzzleSiteAuthenticator\SiteConfig;

class ArraySiteConfigBuilder implements SiteConfigBuilder
{
    /**
     * Map of hostname => SiteConfig.
     */
    private $configs = [];

    private $defaultConfig;

    public function __construct(array $hostConfigMap = [])
    {
        foreach ($hostConfigMap as $host => $hostConfig) {
            $hostConfig['host'] = $host;
            $this->configs[$host] = new SiteConfig($hostConfig);
        }

        $this->defaultConfig = new SiteConfig([]);
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

        return $this->defaultConfig;
    }
}
