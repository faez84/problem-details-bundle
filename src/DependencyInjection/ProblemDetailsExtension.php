<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

final class ProblemDetailsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('problem_details.enabled', $config['enabled']);
        $container->setParameter('problem_details.default_type', $config['default_type']);
        $container->setParameter('problem_details.request_id_header', $config['request_id_header']);
        $container->setParameter('problem_details.set_response_request_id_header', $config['set_response_request_id_header']);

        $container->setParameter('problem_details.apply_when', $config['apply_when']);

        $container->setParameter('problem_details.validation_status', $config['validation_status']);
        $container->setParameter('problem_details.validation_type', $config['validation_type']);
        $container->setParameter('problem_details.validation_title', $config['validation_title']);
        $container->setParameter('problem_details.validation_error_code', $config['validation_error_code']);

        $container->setParameter('problem_details.include_exception_class', $config['include_exception_class']);
        $container->setParameter('problem_details.include_trace', $config['include_trace']);

        $container->setParameter('problem_details.map_exceptions', $config['map_exceptions']);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.php');
    }
}
