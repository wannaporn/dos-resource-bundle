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

        return $treeBuilder;
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
