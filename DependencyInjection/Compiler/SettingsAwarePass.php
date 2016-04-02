<?php

namespace DoS\ResourceBundle\DependencyInjection\Compiler;

use DoS\ResourceBundle\Settings\SettingsAwareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SettingsAwarePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('sylius.settings.manager')) {
            return;
        }

        foreach ($container->findTaggedServiceIds('dos.settings_aware') as $id => $attributes) {
            if (!array_key_exists('alias', $attributes[0])) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must define the "alias" attribute on "dos.settings_aware" tags.', $id));
            }

            $alias = $attributes[0]['alias'];
            $service = $container->getDefinition($id);
            $class = $service->getClass();

            if (strpos($class, '%') !== false) {
                $class = $container->getParameter(str_replace('%', '', $class));
            }

            if (!in_array(SettingsAwareInterface::class, class_implements($class))) {
                throw new \InvalidArgumentException(sprintf('Service "%s" must be implemented of the "%s".', $id, SettingsAwareInterface::class));
            }

            $service->addMethodCall('setSettings', array(new Reference('sylius.settings.manager'), $alias));
        }
    }
}
