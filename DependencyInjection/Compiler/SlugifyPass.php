<?php

namespace DoS\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SlugifyPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cocur_slugify')) {
            return;
        }

        $definition = $container->getDefinition('cocur_slugify');
        $definition->addMethodCall('setRegExp', array(
            $container->getParameter('dos.slugify.reg_exp')
        ));
    }
}
