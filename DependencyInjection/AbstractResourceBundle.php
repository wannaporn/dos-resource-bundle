<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\AbstractResourceBundle as BaseAbstractResourceBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\Container;

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

    /**
     * Dos expected  alias (dos_xx not do_s_xx).
     *
     * @return string
     */
    public static function expectedAlias($name)
    {
        return Container::underscore(preg_replace('/^DoS/', 'Dos', $name));
    }

    /**
     * {@inheritdoc}
     */
    protected function getBundlePrefix()
    {
        return static::expectedAlias(substr(strrchr(get_class($this), '\\'), 1, -6));
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $class = $this->getContainerExtensionClass();
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $basename = preg_replace('/Bundle$/', '', $this->getName());
                $expectedAlias = static::expectedAlias($basename);

                if ($expectedAlias != $extension->getAlias()) {
                    throw new \LogicException(sprintf(
                        'Users will expect the alias of the default extension of a bundle to be the underscored version of the bundle name ("%s"). You can override "Bundle::getContainerExtension()" if you want to use "%s" or another alias.',
                        $expectedAlias, $extension->getAlias()
                    ));
                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        if ($this->extension) {
            return $this->extension;
        }
    }
}
