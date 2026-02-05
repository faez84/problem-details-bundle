<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Faez84\ProblemDetailsBundle\EventSubscriber\ProblemDetailsExceptionSubscriber;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()->defaults()->autowire()->autoconfigure();

    $services->set(ProblemDetailsExceptionSubscriber::class)
        ->tag('kernel.event_subscriber')
        ->arg('$applyWhen', [
            'path_prefixes' => ['/api'],
            'accept_contains' => ['application/json', 'application/problem+json'],
            'content_type_contains' => ['application/json', 'application/ld+json'],
        ])
        ->arg('$expose500Message', true);
};
