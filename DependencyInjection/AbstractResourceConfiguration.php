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
     * @param array $resources
     *
     * @return ArrayNodeDefinition
     */
    protected function createResourcesSection(array $resources = array())
    {
        $builder = new TreeBuilder();
        $node = $builder->root('resources');
        $node->addDefaultsIfNotSet();
        $resourceNodes = $node->children();

        foreach ($resources as $resource => $defaults) {
            $resourceNode = $resourceNodes
                ->arrayNode($resource)
                ->addDefaultsIfNotSet()
            ;

            $this->addClassesSection($resourceNode, $defaults['classes']);

            if (isset($defaults['options'])) {
                $this->createOptionsNode($resourceNode, $defaults['options']);
            }

            if (!isset($defaults['validation_groups'])) {
                $defaults['validation_groups'] = array(
                    'default' => array()
                );
            }

            $this->addValidationGroupsSection($resourceNode, $defaults['validation_groups']);
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
                ->arrayNode('classes')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('model')
                        ->cannotBeEmpty()
                        ->defaultValue(isset($defaults['model']) ? $defaults['model'] : null)
                    ->end()

                    ->scalarNode('factory')
                        ->cannotBeEmpty()
                        ->defaultValue(isset($defaults['factory']) ? $defaults['factory'] : 'DoS\ResourceBundle\Factory\Factory')
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

    protected function createOptionsNode(ArrayNodeDefinition $node, $default = 'default')
    {
        $node
            ->children()
                ->variableNode('options')->end()
            ->end()
        ;
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
            ->end()
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
                ->children()
        ;

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
            $classes['choice'] = 'DoS\ResourceBundle\Form\Type\ResourceChoiceType';
        }

        $node
            ->defaultValue($classes)
                ->useAttributeAsKey('name')
                ->prototype('scalar')
            ->end()
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
            'resources' => array(),
        ), $configs);

        $node->append($this->createDriverNode($configs['driver']));
        $node->append($this->createResourcesSection($configs['resources']));
    }
}
