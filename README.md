# Guzzle site authenticator

[![Build Status](https://travis-ci.org/bdunogier/guzzle-site-authenticator.svg)](https://travis-ci.org/bdunogier/guzzle-site-authenticator)

This package is a plugin for [guzzle](http://packagist.org/packages/guzzlehttp/guzzle) 5.x. It provides a subscriber
that can authenticate requests by posting login information.

It comes up as a Symfony bundle and a generic php lib.

## Installation

### Using composer
Add the package to your requirements using composer: `composer require bdunogier/guzzle-site-authenticator`.

If you're using the Symfony fullstack, add `BD\GuzzleSiteAuthenticatorBundle\BDGuzzleSiteAuthenticatorBundle` to your
kernel class.

## Usage
The guzzle subscriber, `Guzzle\AuthenticatorSubscriber`, must be attached to the Guzzle client. It is provided by the
bundle as `@bd_guzzle_site_authenticator.authenticator_subscriber`:

```
$client = new GuzzleHttp\Client(['defaults' => ['cookies' => new FileCookieJar('/tmp/cookiejar.json']]);
$client->getEmitter()->attach(
  $container->get('bd_guzzle_site_authenticator.authenticator_subscriber')
);
```

### Cookies handling
The `CookieJar` passed to the guzzle client defaults is important: it will be used read/write cookies received by Guzzle,
and is required for authentication to work.

Send a request with Guzzle. If the request's host has a SiteConfig that requires configuration (see below), the plugin
will try to log in to the site if it does not have a cookie yet. After a request, if the response contains the not logged
in text (matched by xpath), it tries to login again, and retries the request.

## Site configuration
Login to sites configured via `SiteConfig` objects:
```php
$siteConfig = new BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig([
  'host' => 'example.com',
  'loginUri' => 'http://example.com/login',
  'usernameField' => 'username',
  'passwordField' => 'password',
  'extraFields' => ['action' => 'login'],
  'notLoggedInXpath' => "//div[@class='not-logged-in']",
  'username' => "johndoe",
  'password' => "unknown",
]);
```

`SiteConfig` objects are returned by a `SiteConfigBuilder`. The library comes with a default `ArraySiteConfigBuilder,
that accepts a list of site config properties array, indexed by host. With the bundle, its contents can be configured
using the `bd_guzzle_site_authenticator.site_config` container variable:

```
# config.yml
parameters:
  bd_guzzle_site_authenticator.site_config:
    example.com:
      host: "example.com"
      loginUri: "http://example.com/login"
      usernameField: "username"
      passwordField: "password"
      extraFields: {action: login}
      notLoggedInXpath: "//div[@class='not-logged-in']"
      username: "johndoe"
      password: "unknown"
    otherexample.com:
      host: ...
```

## Credentials
Credentials (username, password,...) are provided by a `SiteCredentialsProvider`.

The library comes with an `ArraySiteConfigProvider` that is constructed with a hash of hostname => credentials:

```php
$credentialsProvider = new BD\GuzzleSiteAuthenticator\Credentials\ArraySiteCredentialsProvider([
    'example.com' => ['username' => 'johndoe', 'password' => 'jane1702'],
]);
```

### Custom providers
You are encouraged to write your own `SiteConfigProvider` to match your system. A provider must return `Credentials`
objects (`getSiteCredentials()`), and must also implement `hasSiteCredentials()`.

## Implementations
Used in a pull-request to [wallabag](http://github.com/wallabag/wallabag), a read it later web application, to fetch
content from sites that require a login.

It uses the GrabySiteConfigBuilder, and its own authenticator, to fetch content that requires an account.
