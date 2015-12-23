<?php

namespace DoS\ResourceBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FormFactoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('dos.form.factory.class')) {
            return;
        }

        $class = $container->getParameter('dos.form.factory.class');

        $container->setParameter('form.facotry.class', $class);
        $container->getDefinition('form.factory')
            ->setClass($class)
            ->addMethodCall(
                'setSecurityTokenStorage',
                array(new Reference('security.token_storage'))
            )
            ->addMethodCall(
                'setPrefixAndReplacement',
                array(
                    $container->getParameter('dos.form.factory.override_pattern'),
                    $container->getParameter('dos.form.factory.override_replacement'),
                )
            )
        ;
    }
}
