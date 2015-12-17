<?php

namespace Tisseo\BoaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tisseo_boa', 'array');
        $rootNode->children()
            ->arrayNode('datatable_views')
                ->children()
                    ->arrayNode('calendar_mixte')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('name')->end()
                                ->scalarNode('index')->defaultNull(0)->end()
                            ->end()
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('lineVersion')->end()
                                ->scalarNode('index')->defaultNull(1)->end()
                            ->end()
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('computedStartDate')->end()
                                ->scalarNode('index')->defaultNull(2)->end()
                            ->end()
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('computedEndDate')->end()
                                ->scalarNode('index')->defaultNull(3)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->children()
                    ->arrayNode('default_view')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('name')->end()
                                ->scalarNode('index')->defaultNull(0)->end()
                            ->end()
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('computedStartDate')->end()
                                ->scalarNode('index')->defaultNull(1)->end()
                            ->end()
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('computedEndDate')->end()
                                ->scalarNode('index')->defaultNull(2)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->children()
                    ->arrayNode('city_edit')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('id')->end()
                                ->scalarNode('index')->defaultValue(0)->end()
                            ->end()
                            ->children()
                                ->scalarNode('colDbName')->defaultValue('longName')->end()
                                ->scalarNode('index')->defaultValue(1)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
