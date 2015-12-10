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
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

class AbstractResourceExtension extends BaseAbstractResourceExtension
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

        $config = parent::configure($config, $configuration, $container, $configure);

        if (isset($this->loaded['menus'])) {
            $container->setParameter('dos.menus',
                array_replace_recursive($menus, $container->getParameter('dos.menus'))
            );
        }

        return $config;
    }

    protected function registerFormTypes(array $config, ContainerBuilder $container)
    {
        foreach ($config['classes'] as $model => $serviceClasses) {
            if (!isset($serviceClasses['form']) || !is_array($serviceClasses['form'])) {
                continue;
            }

            if ($this->isTranslationSupported() && isset($serviceClasses['translation'])) {
                $this->registerFormTypes(array('classes' => array(sprintf('%s_translation', $model) => $serviceClasses['translation'])), $container);
            }

            foreach ($serviceClasses['form'] as $name => $class) {
                $suffix = ($name === self::DEFAULT_KEY ? '' : sprintf('_%s', $name));
                $definitionId = sprintf('%s.form.type.%s%s', $this->applicationName, $model, $suffix);

                // check definition already exists.
                if ($container->hasDefinition($definitionId)) {
                    continue;
                }

                $alias = sprintf('%s_%s%s', $this->applicationName, $model, $suffix);
                // make sure to valid form's name.
                $alias = preg_replace('/[^a-z0-9_]/i', '_', $alias);

                $definition = new Definition($class);

                if ('choice' === $name) {
                    $definition->setArguments(array($serviceClasses['model'], $config['driver'], $alias));
                } else {
                    $validationGroupsParameterName = sprintf('%s.validation_group.%s%s', $this->applicationName, $model, $suffix);
                    $validationGroups = array('Default');

                    if ($container->hasParameter($validationGroupsParameterName)) {
                        $validationGroups = new Parameter($validationGroupsParameterName);
                    }

                    $definition->setArguments(array($serviceClasses['model'], $validationGroups, $alias));
                }

                if (method_exists($class, 'setObjectManager')) {
                    $definition->addMethodCall(
                        'setObjectManager',
                        array(new Reference(sprintf('%s.manager.%s', $this->applicationName, $model)))
                    );
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

        $container->setParameter($this->getAlias().'_interfaces', $interfaces);
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

    /**
     * Check existing before load!
     *
     * @inheritedoc
     */
    protected function loadServiceDefinitions(ContainerBuilder $containerBuilder, $files)
    {
        $locator = new FileLocator($this->getDefinitionPath());

        $resolver = new LoaderResolver(
            array(
                new XmlFileLoader($containerBuilder, $locator),
                new YamlFileLoader($containerBuilder, $locator),
            ) + $this->getExtraLoaders($containerBuilder)
        );

        $loader = new DelegatingLoader($resolver);

        if (!is_array($files)) {
            $files = array($files);
        }

        foreach ($files as $file) {
            if (file_exists($this->getDefinitionPath() .'/'.$file)) {
                $loader->load($file);
            }
        }
    }

    /**
     * Are translations supported in this app?
     *
     * @return bool
     */
    private function isTranslationSupported()
    {
        return class_exists('Sylius\Bundle\TranslationBundle\DependencyInjection\Mapper');
    }
}
