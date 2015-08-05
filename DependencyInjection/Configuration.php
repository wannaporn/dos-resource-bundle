<?php

namespace DoS\ResourceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class Configuration extends AbstractResourceConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dos_resource');

        $this->addSettingsSection($rootNode);
        $this->addFormFactoryNode($rootNode);
        $this->addSlugifyNode($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds `settings` section.
     *
     * @param $node
     */
    private function addSlugifyNode(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('slugify')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('reg_exp')->defaultValue('/([^A-Za-z0-9\p{Thai}]|-)+/u')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds `settings` section.
     *
     * @param $node
     */
    private function addFormFactoryNode(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('form_factory')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->variableNode('class')->defaultValue('DoS\ResourceBundle\Form\Factory')->end()
                        ->variableNode('pattern')->defaultValue('/(sylius|fos|core)_/')->end()
                        ->variableNode('replacement')->defaultValue('dos_')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds `settings` section.
     *
     * @param $node
     */
    private function addSettingsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('settings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->variableNode('paginate')->defaultNull()->end()
                        ->variableNode('limit')->defaultNull()->end()
                        ->arrayNode('allowed_paginate')
                            ->prototype('integer')->end()
                            ->defaultValue(array(10, 20, 30))
                        ->end()
                        ->integerNode('default_page_size')->defaultValue(20)->end()
                        ->booleanNode('sortable')->defaultFalse()->end()
                        ->variableNode('sorting')->defaultNull()->end()
                        ->booleanNode('filterable')->defaultFalse()->end()
                        ->variableNode('criteria')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
