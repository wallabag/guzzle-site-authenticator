<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\Credentials;

class ArraySiteCredentialsProvider implements SiteCredentialsProvider
{
    /**
     * @var array
     */
    private $sitesCredentials;

    /**
     * ArraySiteCredentialsProvider constructor.
     *
     * @param array $sitesCredentials
     */
    public function __construct(array $sitesCredentials)
    {
        $this->sitesCredentials = $sitesCredentials;
    }

    /**
     * @return \BD\GuzzleSiteAuthenticator\Credentials\Credentials
     */
    public function getSiteCredentials($site)
    {
        if (!isset($this->sitesCredentials[$site])) {
            throw new \OutOfRangeException("No credentials for site '$site''");
        }

        return $this->sitesCredentials[$site];
    }

    /**
     * @return bool
     */
    public function hasSiteCredentials($site)
    {
        return isset($this->sitesCredentials[$site]);
    }
}
