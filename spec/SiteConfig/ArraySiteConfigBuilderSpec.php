<?php

namespace spec\BD\GuzzleSiteAuthenticator\SiteConfig;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use PhpSpec\ObjectBehavior;
use BD\GuzzleSiteAuthenticator\SiteConfig\ArraySiteConfigBuilder;

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

    public function it_returns_site_config_that_exists(SiteConfig $siteConfig)
    {
        $this->buildForHost('example.com')->shouldReturnAnInstanceOf(SiteConfig::class);
    }

    public function it_returns_false_on_a_host_that_does_not_exist()
    {
        $this->buildForHost('anotherexample.com')->shouldReturn(false);
    }
}
