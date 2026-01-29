<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\Request;

use Symfony\Component\HttpFoundation\Request;

final class RequestIdProvider
{
    public function __construct(private string $headerName = 'X-Request-Id')
    {
    }

    public function headerName(): string
    {
        return $this->headerName;
    }

    public function getOrCreate(Request $request): string
    {
        $existing = $request->headers->get($this->headerName);
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        // 16 bytes => 32 hex chars, good enough and portable
        $id = bin2hex(random_bytes(16));
        $request->headers->set($this->headerName, $id);

        return $id;
    }
}
