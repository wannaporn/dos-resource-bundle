<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class DoSResourceExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->configure($configs, new Configuration(), $container,
            self::CONFIGURE_LOADER
        );
    }

    /**
     * @inheritdoc
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        // use the Configuration class to generate a config array with
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->prependExtensionConfig('sylius_resource', $config);
    }
}
