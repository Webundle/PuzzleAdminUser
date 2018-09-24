<?php

namespace Puzzle\Admin\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('admin_user');
        
        $rootNode
            ->children()
                ->scalarNode('title')->defaultValue('user.title')->end()
                ->scalarNode('description')->defaultValue('user.description')->end()
                ->scalarNode('icon')->defaultValue('user.icon')->end()
                ->arrayNode('roles')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('user')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('ROLE_USER')->end()
                                ->scalarNode('description')->defaultValue('user.role.user')->end()
                            ->end()
                        ->end()
                        ->arrayNode('user_manage')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('ROLE_USER_MANAGE')->end()
                                ->scalarNode('description')->defaultValue('user.role.user_manage')->end()
                            ->end()
                        ->end()
                        ->arrayNode('admin')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('ROLE_ADMIN')->end()
                                ->scalarNode('description')->defaultValue('user.role.admin')->end()
                            ->end()
                        ->end()
                        ->arrayNode('super_admin')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('ROLE_SUPER_ADMIN')->end()
                                ->scalarNode('description')->defaultValue('user.role.super_admin')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
