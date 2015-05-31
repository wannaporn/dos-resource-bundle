<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

abstract class AbstractResourceConfiguration implements ConfigurationInterface
{
    const DEFAULT_KEY = 'default';

    /**
     * @param ArrayNodeDefinition $node
     * @param null                $driver
     * @param null                $objectManager
     * @param array               $validationGroups
     *
     * @return $this
     *
     * @deprecated
     */
    protected function addDefaults(ArrayNodeDefinition $node, $driver = null, $objectManager = null, array $validationGroups = array())
    {
        if ($driver) {
            $node->append($this->createDriverNode($driver));
        }

        if ($objectManager) {
            $node->append($this->createObjectManagerNode($objectManager));
        }

        $node->append($this->createTemplatesNode());
        $this->addValidationGroupsSection($node, $validationGroups);

        return $this;
    }

    /**
     * @param array $resources
     *
     * @return ArrayNodeDefinition
     */
    protected function createResourcesSection(array $resources = array())
    {
        $builder = new TreeBuilder();
        $node = $builder->root('classes');
        $node->addDefaultsIfNotSet();
        $resourceNodes = $node->children();

        foreach ($resources as $resource => $defaults) {
            $resourceNode = $resourceNodes
                ->arrayNode($resource)
                ->addDefaultsIfNotSet()
            ;

            $this->addClassesSection($resourceNode, $defaults);
        }

        return $node;
    }

    /**
     * @param ArrayNodeDefinition $node
     * @param array               $defaults
     *
     * @return ArrayNodeDefinition
     */
    protected function addClassesSection(ArrayNodeDefinition $node, array $defaults = array())
    {
        $node
            ->children()
                ->scalarNode('model')
                    ->cannotBeEmpty()
                    ->defaultValue(isset($defaults['model']) ? $defaults['model'] : null)
                ->end()

                ->scalarNode('controller')
                    ->defaultValue(isset($defaults['controller']) ? $defaults['controller'] : 'DoS\ResourceBundle\Controller\ResourceController')
                ->end()

                ->scalarNode('repository')
                    ->cannotBeEmpty()
                    ->defaultValue(isset($defaults['repository']) ? $defaults['repository'] : 'DoS\ResourceBundle\Doctrine\ORM\EntityRepository')
                ->end()

                ->append($this->createInterfaceNode(isset($defaults['interface']) ? $defaults['interface'] : null))
                ->append($this->createFormsNode(isset($defaults['form']) ? $defaults['form'] : null))
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @param string $default
     *
     * @return ScalarNodeDefinition
     */
    protected function createDriverNode($default = null)
    {
        $builder = new TreeBuilder();
        $node = $builder->root('driver', 'enum');

        if ($default) {
            $node->defaultValue($default);
        }

        $node
            ->values(array(
                SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
                SyliusResourceBundle::DRIVER_DOCTRINE_MONGODB_ODM,
                SyliusResourceBundle::DRIVER_DOCTRINE_PHPCR_ODM,
            ))
            ->cannotBeEmpty()
            ->info(sprintf(
                'Database driver (%s, %s or %s)',
                SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
                SyliusResourceBundle::DRIVER_DOCTRINE_MONGODB_ODM,
                SyliusResourceBundle::DRIVER_DOCTRINE_PHPCR_ODM
            ))
            ->end()
        ;

        return $node;
    }

    /**
     * @param string $default
     *
     * @return ScalarNodeDefinition
     */
    protected function createObjectManagerNode($default = 'default')
    {
        $builder = new TreeBuilder();
        $node = $builder->root('object_manager', 'scalar');

        if ($default) {
            $node->defaultValue($default);
        }

        $node
            ->cannotBeEmpty()
            ->info('Name of object Manager')
            ->end();

        return $node;
    }

    /**
     * @param string $default
     *
     * @return ScalarNodeDefinition
     */
    protected function createInterfaceNode($default = null)
    {
        $builder = new TreeBuilder();
        $node = $builder->root('interface', 'scalar');

        if ($default) {
            $node->defaultValue($default);
        }

        $node
            ->cannotBeEmpty()
            ->info('Name of model interface')
            ->end();

        return $node;
    }

    /**
     * @param string $default
     *
     * @return ScalarNodeDefinition
     */
    protected function createTemplateNode($default = null)
    {
        $builder = new TreeBuilder();
        $node = $builder->root('templates', 'scalar');

        if ($default) {
            $node->defaultValue($default);
        }

        $node
            ->info('Template namespace used by each resource')
            ->cannotBeEmpty()
            ->end();

        return $node;
    }

    /**
     * @return ArrayNodeDefinition
     */
    protected function createTemplatesNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('templates');

        $node
            ->useAttributeAsKey('name')
            ->prototype('scalar')->end()
            ->end();

        return $node;
    }

    /**
     * @param array $default
     *
     * @return ArrayNodeDefinition
     */
    protected function createValidationGroupNode(array $default = array())
    {
        $builder = new TreeBuilder();
        $node = $builder->root('validation_group');

        $node
            ->info('Validation groups used by the form component')
            ->prototype('scalar')->defaultValue($default)->end()
        ;

        return $node;
    }

    /**
     * @param ArrayNodeDefinition $node
     * @param array               $validationGroups
     */
    protected function addValidationGroupsSection(ArrayNodeDefinition $node, array $validationGroups = array())
    {
        $child = $node
            ->children()
            ->arrayNode('validation_groups')
            ->addDefaultsIfNotSet()
            ->children();

        foreach ($validationGroups as $name => $groups) {
            $child
                ->arrayNode($name)
                ->prototype('scalar')->end()
                ->defaultValue($groups)
                ->end();
        }

        $child
            ->end()
            ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    protected function addTemplatesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('templates')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')
                ->end()
            ->end()
        ;
    }

    /**
     * @param array|string $classes
     *
     * @return ArrayNodeDefinition
     */
    protected function createFormsNode($classes)
    {
        $builder = new TreeBuilder();
        $node = $builder->root('form');

        if (is_string($classes)) {
            $classes = array(self::DEFAULT_KEY => $classes);
        }

        if (!isset($classes['choice'])) {
            $classes['choice'] = 'Sylius\Bundle\ResourceBundle\Form\Type\ResourceChoiceType';
            //$classes['choice'] = '%dos.form.type.choice.default.class%';
        }

        $node
            ->info('')
            ->defaultValue($classes)
            ->useAttributeAsKey('name')
            ->prototype('scalar')->end()
            ->beforeNormalization()
            ->ifString()
            ->then(function ($v) {
                return array(
                    AbstractResourceConfiguration::DEFAULT_KEY => $v,
                );
            })
            ->end()
        ;

        return $node;
    }

    protected function setDefaults(ArrayNodeDefinition $node, array $configs = array())
    {
        $configs = array_replace_recursive(array(
            'driver' => SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
            'object_manager' => 'default',
            'templates' => null,
            'classes' => array(),
            'validation_groups' => array(),
        ), $configs);

        $node->append($this->createDriverNode($configs['driver']));
        $node->append($this->createObjectManagerNode($configs['object_manager']));
        $node->append($this->createTemplateNode($configs['templates']));
        $node->append($this->createResourcesSection($configs['classes']));

        $this->addValidationGroupsSection($node, $configs['validation_groups']);
    }
}
