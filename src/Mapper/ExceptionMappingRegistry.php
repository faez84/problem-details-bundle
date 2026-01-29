<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\Mapper;

final class ExceptionMappingRegistry
{
    /**
     * @param array<string, array{type?:?string,title?:?string,detail?:?string,error_code?:?string,status?:?int}> $mappings
     */
    public function __construct(
        private array $mappings,
        private string $defaultType = 'about:blank'
    ) {}

    public function defaultType(): string
    {
        return $this->defaultType;
    }

    /**
     * @return array{type?:?string,title?:?string,detail?:?string,error_code?:?string,status?:?int}|null
     */
    public function findFor(string $exceptionClass): ?array
    {
        // exact match first
        if (isset($this->mappings[$exceptionClass])) {
            return $this->mappings[$exceptionClass];
        }

        // allow base class mapping
        foreach ($this->mappings as $class => $mapping) {
            if (is_a($exceptionClass, $class, true)) {
                return $mapping;
            }
        }

        return null;
    }
}
