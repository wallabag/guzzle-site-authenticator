<?php

namespace BD\GuzzleSiteAuthenticator\Graby;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use Graby\SiteConfig\ConfigBuilder;
use OutOfRangeException;

/**
 * A config builder that uses graby's builder to generate the configuration.
 * Requires that `j0k3r/graby` is installed.
 */
class GrabySiteConfigBuilder implements SiteConfigBuilder
{
    /**
     * @var \Graby\SiteConfig\ConfigBuilder
     */
    private $grabyConfigBuilder;

    /**
     * GrabySiteConfigBuilder constructor.
     *
     * @param \Graby\SiteConfig\ConfigBuilder $grabyConfigBuilder
     */
    public function __construct(ConfigBuilder $grabyConfigBuilder)
    {
        $this->grabyConfigBuilder = $grabyConfigBuilder;
    }

    /**
     * Builds the SiteConfig for a host.
     *
     * @param string $host The "www." prefix is ignored.
     *
     * @return SiteConfig
     *
     * @throws OutOfRangeException If there is no config for $host
     */
    public function buildForHost($host)
    {
        $config = $this->grabyConfigBuilder->buildForHost($host);
        $parameters = [
            'host' => $host,
            'requiresLogin' => $config->requires_login ?: false,
            'loginUri' => $config->login_uri ?: null,
            'usernameField' => $config->login_username_field ?: null,
            'passwordField' => $config->login_password_field ?: null,
            'extraFields' => is_array($config->login_extra_fields) ? $config->login_extra_fields : [],
            'notLoggedInXpath' => $config->not_logged_in_xpath ?: null,
        ];

        return new SiteConfig($parameters);
    }
}
