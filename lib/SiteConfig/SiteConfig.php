<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\SiteConfig;

use InvalidArgumentException;

/**
 * Authentication configuration for a site.
 */
class SiteConfig
{
    /**
     * The site's host name.
     * @var string
     */
    protected $host;

    /**
     * If the site requires a loogin or not.
     * @var bool
     */
    protected $requiresLogin;

    /**
     * XPath query used to check if the user was logged in or not.
     * @var string
     */
    protected $notLoggedInXpath;

    /**
     * URI login data must be sent to.
     * @var string
     */
    protected $loginUri;

    /**
     * Name of the username field.
     * @var string
     */
    protected $usernameField;

    /**
     * Name of the password field.
     * @var string
     */
    protected $passwordField;

    /**
     * Associative array of extra fields to send with the form.
     * @var array
     */
    protected $extraFields;

    /**
     * SiteConfig constructor. Sets the properties by name given a hash.
     *
     * @param array $properties
     *
     * @throws \InvalidArgumentException If a property doesn't exist.
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $propertyName => $propertyValue)
        {
            if (!property_exists($this, $propertyName)) {
                throw new InvalidArgumentException("Unknown property $propertyName");
            }
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * @return boolean
     */
    public function requiresLogin()
    {
        return $this->requiresLogin;
    }

    /**
     * @param boolean $requiresLogin
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig
     */
    public function setRequiresLogin($requiresLogin)
    {
        $this->requiresLogin = $requiresLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotLoggedInXpath()
    {
        return $this->notLoggedInXpath;
    }

    /**
     * @param string $notLoggedInXpath
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig
     */
    public function setNotLoggedInXpath($notLoggedInXpath)
    {
        $this->notLoggedInXpath = $notLoggedInXpath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoginUri()
    {
        return $this->loginUri;
    }

    /**
     * @param string $loginUri
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig
     */
    public function setLoginUri($loginUri)
    {
        $this->loginUri = $loginUri;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsernameField()
    {
        return $this->usernameField;
    }

    /**
     * @param string $usernameField
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig
     */
    public function setUsernameField($usernameField)
    {
        $this->usernameField = $usernameField;

        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordField()
    {

        return $this->passwordField;
    }

    /**
     * @param string $passwordField
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig
     */
    public function setPasswordField($passwordField)
    {
        $this->passwordField = $passwordField;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtraFields()
    {
        return $this->extraFields;
    }

    /**
     * @param array $extraFields
     * @return \BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig
     */
    public function setExtraFields($extraFields)
    {
        $this->extraFields = $extraFields;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return SiteConfig
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
}
}
