<?php

namespace BD\GuzzleSiteAuthenticatorBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterWallabagGuzzleSubscribersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('wallabag_core.guzzle.http_client_factory')) {
            return;
        }

        $definition = $container->getDefinition('wallabag_core.guzzle.http_client_factory');

        // manually add subsribers for some websites
        $definition->addMethodCall(
            'addSubscriber', [
                new Reference('bd_guzzle_site_authenticator.monde_diplomatique_uri_fix_subscriber'),
            ]
        );
    }
}
