<?php declare(strict_types=1);

namespace OAS\Bridge\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return (new TreeBuilder('oas'))
            ->getRootNode()
                ->children()
                    ->arrayNode('specs')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('index_path')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('resolve_path')
                                ->end()
                                ->booleanNode('cache')
                                    ->defaultFalse()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('validation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('validate_always')
                                ->defaultFalse()
                            ->end()
                            ->scalarNode('validate_mutating_requests_only')
                                ->defaultTrue()
                            ->end()
                            ->scalarNode('raise_error_on_missing_schema')
                                ->defaultTrue()
                            ->end()
                            ->scalarNode('yield_format_specific_error')
                                ->defaultTrue()
                            ->end()
                            ->arrayNode('validator_options')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('yield_format_specific_error')
                                        ->defaultTrue()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}