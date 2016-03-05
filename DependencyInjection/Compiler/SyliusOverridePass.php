<?php

namespace DoS\ResourceBundle\DependencyInjection\Compiler;

use DoS\ResourceBundle\Controller\RequestConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SyliusOverridePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter(
            'sylius.expression_language.class', $container->getParameter('dos.expression_language.class')
        );

        $container->setParameter(
            'sylius.resource_controller.request_configuration.class', RequestConfiguration::class
        );
    }
}
