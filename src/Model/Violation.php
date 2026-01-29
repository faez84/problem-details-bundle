<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\Model;

final class Violation
{
    public function __construct(
        public readonly string $field,
        public readonly string $message
    ) {}
}
