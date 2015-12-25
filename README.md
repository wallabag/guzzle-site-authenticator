# Guzzle site authenticator

[![Build Status](https://travis-ci.org/bdunogier/guzzle-site-authenticator.svg)](https://travis-ci.org/bdunogier/guzzle-site-authenticator)

This package is a plugin for [guzzle](http://packagist.org/packages/guzzlehttp/guzzle) 5.x. It provides a subscriber that can authenticate requests by posting login information.

It comes up as a Symfony bundle and a generic php lib.

## Installation

### Using composer
Add the package to your requirements using composer: `composer require bdunogier/guzzle-site-authenticator`.

If you're using the Symfony fullstack, add `BD\GuzzleSiteAuthenticatorBundle\BDGuzzleSiteAuthenticatorBundle` to your
kernel class.

## Usage
The `BD\GuzzleSiteAuthenticator\Guzzle\AuthenticatorSubscriber` must be attached to the Guzzle client. The `bd_guzzle_site_authenticator.authenticator_subscriber` can be used for this, for instance via a factory:

```
$client = new GuzzleHttp\Client(['defaults' => ['cookies' => new FileCookieJar('/tmp/cookiejar.json']]);
$client->getEmitter()->attach(
  $container->get('bd_guzzle_site_authenticator.authenticator_subscriber')
);
```

The `CookieJar` is important: it will be used read/write cookies received by Guzzle, and is required for authentication
to work.

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
  'notLoggedInXpath' => "//div[@class='not-logged-in']"
]);
```

`SiteConfig` objects are returned by a `SiteConfigBuilder`. The library comes with a default `ArraySiteConfigBuilder.
Its contents can be configured using the `bd_guzzle_site_authenticator.site_config` variable:

```
# config.yml
parameters:
  bd_guzzle_site_authenticator.site_config:
    example.com:
      host: "example.com"
      loginUri: "http://example.com/login"
      username_field: "username"
      password_field: "password"
      extra_fields: {action: login}
      not_logged_in_xpath: "//div[@class='not-logged-in']"
    otherexample.com:
      host: ...
```

## Credentials
Credentials for login to sites is handled by the `CredentialBag` interface.
You can re-use the default `ArrayCredentialBag`, that receives the `bd_guzzle_site_authenticator.credentials` container
variable:

```
# config.yml
parameters:
  bd_guzzle_site_authenticator.credentials:
    example.com: {username: "johndoe", password: "unknown"}
```
