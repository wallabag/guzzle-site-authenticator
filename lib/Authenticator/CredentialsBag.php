<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\Authenticator;

/**
 * A bag of credentials per host.
 */
interface CredentialsBag
{
    /**
     * @param string $host
     *
     * @return array Array with username and password keys.
     * @throw \OutOfRangeException if there are no credentials for this host.
     */
    public function getCredentialsForHost($host);

    /**
     * @param string $host
     *
     * @return bool
     */
    public function hasCredentialsForHost($host);
}
