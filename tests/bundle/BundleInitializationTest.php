<?php

namespace Tests\BD\GuzzleSiteAuthenticatorBundle;

use BD\GuzzleSiteAuthenticatorBundle\BDGuzzleSiteAuthenticatorBundle;
use Nyholm\BundleTest\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class BundleInitializationTest extends KernelTestCase
{
    public function testInitBundle(): void
    {
        $kernel = self::bootKernel();

        $this->assertInstanceOf(TestKernel::class, $kernel);
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(BDGuzzleSiteAuthenticatorBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }
}
