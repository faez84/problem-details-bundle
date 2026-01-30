<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Faez84\ProblemDetailsBundle\EventSubscriber\ProblemDetailsExceptionSubscriber;


return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ProblemDetailsExceptionSubscriber::class)
        ->tag('kernel.event_subscriber');
};
