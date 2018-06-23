<?php

namespace spec\BD\GuzzleSiteAuthenticator\SiteConfig;

use BD\GuzzleSiteAuthenticator\SiteConfig\ArraySiteConfigBuilder;
use BD\GuzzleSiteAuthenticator\SiteConfig\NullSiteConfig;
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
        $this->shouldHaveType(ArraySiteConfigBuilder::class);
    }

    public function it_returns_site_config_that_exists()
    {
        $this->buildForHost('example.com')->shouldReturnAnInstanceOf(SiteConfig::class);
        $this->buildForHost('example.com')->shouldNotBeAnInstanceOf(NullSiteConfig::class);
    }

    public function it_returns_a_null_site_config_on_a_host_that_does_not_exist()
    {
        $this->buildForHost('anotherexample.com')->shouldBeAnInstanceOf(NullSiteConfig::class);
    }
}
