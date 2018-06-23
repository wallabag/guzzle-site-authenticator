<?php

namespace BD\GuzzleSiteAuthenticator\HttpClient;

use BD\GuzzleSiteAuthenticator\Authenticator\Factory;
use BD\GuzzleSiteAuthenticator\Plugin\AuthenticatorPlugin;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin\CookiePlugin;
use Http\Client\Common\Plugin\RedirectPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\CookieJar;

/**
 * @see http://docs.php-http.org/en/latest/plugins/introduction.html#libraries-that-require-plugins
 */
class HttpClientFactory
{
    /**
     * Build the HTTP client to authenticate on the site.
     *
     * @param \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfigBuilder $siteConfigBuilder
     * @param HttpClient                                               $client            Base HTTP client
     *
     * @return HttpMethodsClient
     */
    public static function create(SiteConfigBuilder $siteConfigBuilder, HttpClient $client = null)
    {
        if (!$client) {
            $client = HttpClientDiscovery::find();
        }

        $cookieJar = new CookieJar();
        $cookiePlugin = new CookiePlugin($cookieJar);

        $messageFactory = MessageFactoryDiscovery::find();

        $httpClient = new HttpMethodsClient(
            new PluginClient($client, [$cookiePlugin, new RedirectPlugin()]),
            $messageFactory
        );

        $authHttpClient = new HttpMethodsClient(
            new PluginClient($client, [$cookiePlugin], ['max_restarts' => 2]),
            $messageFactory
        );

        $pluginClient = new PluginClient(
            $httpClient,
            [new AuthenticatorPlugin($siteConfigBuilder, new Factory(), $authHttpClient, $cookieJar)],
            ['max_restarts' => 0] // only one execution
        );

        return new HttpMethodsClient($pluginClient, $messageFactory);
    }
}
