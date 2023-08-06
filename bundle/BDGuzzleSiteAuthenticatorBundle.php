<?php

namespace BD\GuzzleSiteAuthenticatorBundle;

use BD\GuzzleSiteAuthenticatorBundle\DependencyInjection\CompilerPass\RegisterWallabagGuzzleSubscribersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BDGuzzleSiteAuthenticatorBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterWallabagGuzzleSubscribersPass());
    }
}
