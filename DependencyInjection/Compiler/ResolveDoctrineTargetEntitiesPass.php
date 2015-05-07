<?php

namespace DoS\ResourceBundle\DependencyInjection\Compiler;

use Sylius\Bundle\ResourceBundle\DependencyInjection\DoctrineTargetEntitiesResolver;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolveDoctrineTargetEntitiesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $extensionAlias;

    /**
     * @var string
     */
    private $bundlePrefix;

    public function __construct($bundlePrefix, $extensionAlias)
    {
        $this->bundlePrefix = $bundlePrefix;
        $this->extensionAlias = $extensionAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (SyliusResourceBundle::DRIVER_DOCTRINE_ORM === $this->getDriver($container)) {

            $name = $this->extensionAlias . '_interfaces';

            if ($container->hasParameter($name)) {
                $interfaces = $container->getParameter($name);

                if (!empty($interfaces)) {
                    $resolver = new DoctrineTargetEntitiesResolver();
                    $resolver->resolve($container, $interfaces);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string
     */
    private function getDriver(ContainerBuilder $container)
    {
        $name = sprintf('%s.driver', $this->bundlePrefix);

        if ($container->hasParameter($name)) {
            return $container->getParameter($name);
        }

        return;
    }
}
