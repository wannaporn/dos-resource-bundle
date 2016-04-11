<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension as BaseAbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractResourceExtension extends BaseAbstractResourceExtension
{
    protected $applicationName = 'dos';

    protected $configFiles = array(
        'parameters.yml',
        'services.yml',
        'providers.yml',
        'controllers.yml',
        'templatings.yml',
        'listeners.yml',
        'settings.yml',
        'callbacks.yml',
        'forms.yml',
        'mails.yml',
        'menus.yml',
        'twigs.yml',
        'workers.yml',
    );

    /**
     * DoS translate to Dos preventing Container::underscore => do_s_.
     *
     * {@inheritdoc}
     */
    public function getAlias()
    {
        $className = get_class($this);

        if (substr($className, -9) != 'Extension') {
            throw new BadMethodCallException('This extension does not follow the naming convention; you must overwrite the getAlias() method.');
        }

        return AbstractResourceBundle::expectedAlias(substr(strrchr($className, '\\'), 1, -9));
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getBundleConfiguration(), $config);
        $loader = new YamlFileLoader($container, new FileLocator($this->getConfigDir()));

        if (empty($config['resources'])) {
            $config['resources'] = array();
        }

        if (isset($config['driver'])) {
            $this->registerResources($this->applicationName, $config['driver'], $config['resources'], $container);
        }

        foreach($config['resources'] as $model => $resource) {
            foreach($resource['classes'] as $key => $class) {
                if ($key === 'provider') {
                    $name = sprintf('%s.%s.%s.class', $this->applicationName, $key, $model);
                    $container->setParameter($name, $class);
                    $this->addProvider($container, $class, $model);
                }
            }
        }

        foreach ($this->configFiles as $configFile) {
            if (file_exists(sprintf('%s/%s', $this->getConfigDir(), $configFile))) {
                $loader->load($configFile);
            }
        }

        return $config;
    }

    protected function addProvider(ContainerBuilder $container, $providerClass, $modelName)
    {
        $providerReflection = new \ReflectionClass($providerClass);
        $definition = new Definition($providerClass);

        $definition->setArguments([
            new Reference(sprintf('%s.repository.%s', $this->applicationName, $modelName)),
            new Reference(sprintf('%s.factory.%s', $this->applicationName, $modelName)),
        ]);

        $definition->setLazy(!$providerReflection->isFinal());
        $container->setDefinition(sprintf('%s.provider.%s', $this->applicationName, $modelName), $definition);
    }

    /**
     * @return string
     */
    protected function getConfigDir()
    {
        $reflector = new \ReflectionClass($this);
        $fileName = $reflector->getFileName();

        return sprintf('%s/../Resources/config', dirname($fileName));
    }

    /**
     * @return ConfigurationInterface
     */
    abstract protected function getBundleConfiguration();
}
