<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\Authenticator\CredentialsBag;

use BD\GuzzleSiteAuthenticator\Authenticator\CredentialsBag;

/**
 * A simple credential bag based on an array.
 */
class ArrayCredentialsBag implements CredentialsBag
{
    /**
     * @var array
     */
    private $credentials;

    public function __construct(array $credentials = [])
    {
        $this->credentials = $credentials;
    }

    public function getCredentialsForHost($host)
    {
        $host = strtolower($host);
        if (substr($host, 0, 4) == 'www.') {
            $host = substr($host, 4);
        }

        if (isset($this->credentials[$host])) {
            return $this->credentials[$host];
        }
        throw new \OutOfRangeException("No credentials found for $host");
    }

    public function hasCredentialsForHost($host)
    {
        return isset($this->credentials[$host]);
    }
}
