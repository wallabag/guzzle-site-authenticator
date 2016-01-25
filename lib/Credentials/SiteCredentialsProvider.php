<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\Credentials;

interface SiteCredentialsProvider
{
    /**
     * @return \BD\GuzzleSiteAuthenticator\Credentials\Credentials
     */
    public function getSiteCredentials($site);

    /**
     * @return bool
     */
    public function hasSiteCredentials($site);
}
