<?php

namespace BD\GuzzleSiteAuthenticator\Authenticator;

use Http\Client\Common\HttpMethodsClient;
use Psr\Http\Message\ResponseInterface;

interface Authenticator
{
    /**
     * Logs the configured user on the given Http client.
     *
     * @param HttpMethodsClient $httpClient
     *
     * @return false|ResponseInterface
     */
    public function login(HttpMethodsClient $httpClient);

    /**
     * Checks from the HTTP response if authentication is requested by a grabbed page.
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function isLoginRequired(ResponseInterface $response);
}
