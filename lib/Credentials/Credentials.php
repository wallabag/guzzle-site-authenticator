<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace BD\GuzzleSiteAuthenticator\Credentials;

class Credentials
{
    private $username;

    private $password;

    public function __construct($username, $password) {

        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
