<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\Model;

final class ProblemDetails
{
    /**
     * @param Violation[] $violations
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly int $status,
        public readonly ?string $detail,
        public readonly ?string $instance,
        public readonly ?string $errorCode = null,
        public readonly ?string $requestId = null,
        public readonly array $violations = [],
        public readonly array $meta = [],
    ) {}
}
