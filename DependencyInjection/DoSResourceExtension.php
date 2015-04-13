<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoSResourceExtension extends AbstractResourceExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        list($config) = $this->configure($configs, new Configuration(), $container,
            self::CONFIGURE_LOADER
        );
    }
}
