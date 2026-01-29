<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('problem_details');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('default_type')->defaultValue('about:blank')->end()
                ->scalarNode('request_id_header')->defaultValue('X-Request-Id')->end()
                ->booleanNode('set_response_request_id_header')->defaultTrue()->end()

                // Apply only for API requests
                ->arrayNode('apply_when')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('path_prefixes')
                            ->scalarPrototype()->end()
                            ->defaultValue(['/api'])
                        ->end()
                        ->arrayNode('accept_contains')
                            ->scalarPrototype()->end()
                            ->defaultValue(['application/json', 'application/problem+json'])
                        ->end()
                        ->arrayNode('content_type_contains')
                            ->scalarPrototype()->end()
                            ->defaultValue(['application/json', 'application/ld+json'])
                        ->end()
                    ->end()
                ->end()

                // Validation behavior
                ->integerNode('validation_status')->defaultValue(422)->min(400)->max(499)->end()
                ->scalarNode('validation_type')->defaultValue('https://httpstatuses.com/422')->end()
                ->scalarNode('validation_title')->defaultValue('Validation Failed')->end()
                ->scalarNode('validation_error_code')->defaultValue('VALIDATION_FAILED')->end()

                // Debug fields
                ->booleanNode('include_exception_class')->defaultFalse()->end()
                ->booleanNode('include_trace')->defaultFalse()->end()

                // Exception mapping by FQCN
                ->arrayNode('map_exceptions')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('type')->defaultNull()->end()
                            ->scalarNode('title')->defaultNull()->end()
                            ->scalarNode('detail')->defaultNull()->end()
                            ->scalarNode('error_code')->defaultNull()->end()
                            ->integerNode('status')->defaultNull()->min(100)->max(599)->end()
                        ->end()
                    ->end()
                    ->defaultValue([])
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
