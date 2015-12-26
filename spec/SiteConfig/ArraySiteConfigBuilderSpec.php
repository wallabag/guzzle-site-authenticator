<?php

namespace spec\BD\GuzzleSiteAuthenticator\SiteConfig;

use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArraySiteConfigBuilderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['example.com' => []]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('BD\GuzzleSiteAuthenticator\SiteConfig\ArraySiteConfigBuilder');
    }

    function it_returns_site_config_that_exists(SiteConfig $siteConfig)
    {
        $this->buildForHost('example.com')->shouldReturnAnInstanceOf('BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig');
    }

    function it_throws_an_out_of_range_exception_on_a_host_that_does_not_exist()
    {
        $this->shouldThrow('\OutOfRangeException')->duringBuildForHost('anotherexample.com');
    }
}
