<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\DependencyInjection\AbstractResourceExtension as BaseAbstractResourceExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Parameter;

class AbstractResourceExtension extends BaseAbstractResourceExtension
{
    protected $applicationName = 'dos';
    protected $configFormat = self::CONFIG_YAML;

    protected $configFiles = array(
        'parameters',
        'services',
        'providers',
        'templatings',
        'listeners',
        'settings',
        'callbacks',
        'forms',
        'mails',
        'menus',
        'twigs',
    );

    /**
     * @var array
     */
    private $loaded = array();

    public function configure(
        array $config,
        ConfigurationInterface $configuration,
        ContainerBuilder $container,
        $configure = self::CONFIGURE_LOADER
    ) {
        // TODO: on/off this feature
        // NOTE! MUST Order UiBundle First! of all bundles
        $menus = $container->hasParameter('dos.menus')
            ? $container->getParameter('dos.menus')
            : array()
        ;

        list($config, $loader) = parent::configure($config, $configuration, $container, $configure);

        if (isset($this->loaded['menus'])) {
            $container->setParameter('dos.menus',
                array_replace_recursive($menus, $container->getParameter('dos.menus'))
            );
        }

        return array($config, $loader);
    }

    /**
     * @param array           $config
     * @param LoaderInterface $loader
     */
    protected function loadConfigurationFile(array $config, LoaderInterface $loader)
    {
        foreach ($config as $filename) {
            if (file_exists($file = sprintf('%s/%s.%s', $this->getConfigurationDirectory(), $filename, $this->configFormat))) {
                $loader->load($file);
                $this->loaded[$filename] = true;
            }
        }
    }

    protected function registerFormTypes(array $config, ContainerBuilder $container)
    {
        foreach ($config['classes'] as $model => $serviceClasses) {
            if (!isset($serviceClasses['form']) || !is_array($serviceClasses['form'])) {
                continue;
            }

            foreach ($serviceClasses['form'] as $name => $class) {
                $suffix = ($name === self::DEFAULT_KEY ? '' : sprintf('_%s', $name));
                $definitionId = sprintf('%s.form.type.%s%s', $this->applicationName, $model,
                    $suffix);

                // check definition already exists.
                if ($container->hasDefinition($definitionId)) {
                    continue;
                }

                $alias = sprintf('%s_%s%s', $this->applicationName, $model, $suffix);
                // make sure to valid form's name.
                $alias = preg_replace('/[^a-z0-9_]/i', '_', $alias);

                $definition = new Definition($class);

                if ('choice' === $name) {
                    $definition->setArguments(array(
                        $serviceClasses['model'],
                        $config['driver'],
                        $alias,
                    ));
                } else {
                    $definition->setArguments(array(
                        $serviceClasses['model'],
                        new Parameter(sprintf('%s.validation_group.%s%s', $this->applicationName,
                            $model, $suffix)),
                    ));
                }

                $definition->addTag('form.type', array('alias' => $alias));
                $container->setDefinition($definitionId, $definition);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function mapClassParameters(array $classes, ContainerBuilder $container)
    {
        $interfaces = array();
        foreach ($classes as $model => $serviceClasses) {
            foreach ($serviceClasses as $service => $class) {
                if ('form' === $service) {
                    if (!is_array($class)) {
                        $class = array(self::DEFAULT_KEY => $class);
                    }
                    foreach ($class as $suffix => $subClass) {
                        $container->setParameter(
                            sprintf(
                                '%s.form.type.%s%s.class',
                                $this->applicationName,
                                $model,
                                $suffix === self::DEFAULT_KEY ? '' : sprintf('_%s', $suffix)
                            ),
                            $subClass
                        );
                    }
                } elseif ('translation' === $service) {
                    $this->mapClassParameters(array(sprintf('%s_translation', $model) => $class), $container);
                } else {
                    $name = sprintf('%s.%s.%s.class', $this->applicationName, $service, $model);
                    $container->setParameter($name, $class);

                    if ($service === 'interface') {
                        $interfaces[$name] = sprintf('%s.%s.%s.class', $this->applicationName, 'model', $model);
                    }
                }
            }
        }

        $container->setParameter($this->getAlias() . '_interfaces', $interfaces);
    }

    /**
     * DoS translate to Dos preventing Container::underscore => do_s_.
     *
     * @inheritedoc
     */
    public function getAlias()
    {
        $className = get_class($this);

        if (substr($className, -9) != 'Extension') {
            throw new BadMethodCallException('This extension does not follow the naming convention; you must overwrite the getAlias() method.');
        }

        return AbstractResourceBundle::expectedAlias(substr(strrchr($className, '\\'), 1, -9));
    }
}
