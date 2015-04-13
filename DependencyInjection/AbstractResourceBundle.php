<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\AbstractResourceBundle as BaseAbstractResourceBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;

abstract class AbstractResourceBundle extends BaseAbstractResourceBundle
{
    protected $mappingFormat = self::MAPPING_YAML;

    /**
     * {@inheritdoc}
     */
    public static function getSupportedDrivers()
    {
        return array(
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelNamespace()
    {
        $bundle = explode('\\', $class = get_called_class());

        return str_replace($bundle[count($bundle) -1], 'Model', $class);
    }
}
