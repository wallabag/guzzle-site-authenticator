<?php

namespace Tests\BD\GuzzleSiteAuthenticator\SiteConfig;

use BD\GuzzleSiteAuthenticator\SiteConfig\ArraySiteConfigBuilder;
use BD\GuzzleSiteAuthenticator\SiteConfig\SiteConfig;
use PHPUnit\Framework\TestCase;

class ArraySiteConfigBuilderTest extends TestCase
{
    public function testItReturnsSiteConfigThatExists()
    {
        $builder = new ArraySiteConfigBuilder(['example.com' => []]);
        $res = $builder->buildForHost('www.example.com');

        $this->assertInstanceOf(SiteConfig::class, $res);
    }

    public function testItReturnsFalseOnAHostThatDoesNotExist()
    {
        $builder = new ArraySiteConfigBuilder(['anotherexample.com' => []]);
        $res = $builder->buildForHost('example.com');

        $this->assertfalse($res);
    }
}
