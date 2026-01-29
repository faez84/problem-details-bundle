<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Faez84\ProblemDetailsBundle\EventSubscriber\ProblemDetailsExceptionSubscriber;
use Faez84\ProblemDetailsBundle\Mapper\ExceptionMappingRegistry;
use Faez84\ProblemDetailsBundle\Mapper\ExceptionToProblemDetailsMapper;
use Faez84\ProblemDetailsBundle\Normalizer\ProblemDetailsNormalizer;
use Faez84\ProblemDetailsBundle\Request\RequestIdProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RequestIdProvider::class)
        ->arg('$headerName', param('problem_details.request_id_header'));

    $services->set(ExceptionMappingRegistry::class)
        ->arg('$mappings', param('problem_details.map_exceptions'))
        ->arg('$defaultType', param('problem_details.default_type'));

    $services->set(ExceptionToProblemDetailsMapper::class)
        ->arg('$registry', service(ExceptionMappingRegistry::class))
        ->arg('$requestIdProvider', service(RequestIdProvider::class))
        ->arg('$includeExceptionClass', param('problem_details.include_exception_class'))
        ->arg('$includeTrace', param('problem_details.include_trace'))
        ->arg('$validationStatus', param('problem_details.validation_status'))
        ->arg('$validationType', param('problem_details.validation_type'))
        ->arg('$validationTitle', param('problem_details.validation_title'))
        ->arg('$validationErrorCode', param('problem_details.validation_error_code'));

    $services->set(ProblemDetailsNormalizer::class);

    $services->set(ProblemDetailsExceptionSubscriber::class)
        ->tag('kernel.event_subscriber')
        ->arg('$enabled', param('problem_details.enabled'))
        ->arg('$applyWhen', param('problem_details.apply_when'))
        ->arg('$setResponseRequestIdHeader', param('problem_details.set_response_request_id_header'));
};
