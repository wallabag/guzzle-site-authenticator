<?php

namespace spec\BD\GuzzleSiteAuthenticator\SiteConfig;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use PhpSpec\ObjectBehavior;

class ArraySiteConfigBuilderSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['example.com' => []]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('BD\GuzzleSiteAuthenticator\SiteConfig\ArraySiteConfigBuilder');
    }

    public function it_returns_site_config_that_exists(SiteConfig $siteConfig)
    {
        $this->buildForHost('example.com')->shouldReturnAnInstanceOf('BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig');
    }

    public function it_returns_the_default_config_on_a_host_that_does_not_exist()
    {
        $this->buildForHost('anotherexample.com')->shouldReturnAnInstanceOf('BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig');
    }
}
