<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\Mapper;

use Faez84\ProblemDetailsBundle\Model\ProblemDetails;
use Faez84\ProblemDetailsBundle\Model\Violation;
use Faez84\ProblemDetailsBundle\Request\RequestIdProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ExceptionToProblemDetailsMapper
{
    public function __construct(
        private ExceptionMappingRegistry $registry,
        private RequestIdProvider $requestIdProvider,
        private bool $includeExceptionClass,
        private bool $includeTrace,
        private int $validationStatus,
        private string $validationType,
        private string $validationTitle,
        private string $validationErrorCode,
    ) {}

    public function map(Throwable $e, Request $request): ProblemDetails
    {
        $requestId = $this->requestIdProvider->getOrCreate($request);
        $instance = $request->getPathInfo();

        // 1) Validation exception (support both Symfony Validator and custom domain)
        $validation = $this->tryMapValidation($e);
        if ($validation !== null) {
            return new ProblemDetails(
                type: $this->validationType,
                title: $this->validationTitle,
                status: $this->validationStatus,
                detail: 'Your request parameters did not validate.',
                instance: $instance,
                errorCode: $this->validationErrorCode,
                requestId: $requestId,
                violations: $validation,
                meta: $this->debugMeta($e)
            );
        }

        // 2) HttpException
        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        // 3) Mapping overrides
        $mapping = $this->registry->findFor($e::class);

        $type = $mapping['type'] ?? ($status >= 500 ? $this->registry->defaultType() : 'https://httpstatuses.com/' . $status);
        $title = $mapping['title'] ?? ($status >= 500 ? 'Internal Server Error' : $this->fallbackTitle($status));
        $detail = $mapping['detail'] ?? $this->safeDetail($e, $status);
        $errorCode = $mapping['error_code'] ?? null;

        if (isset($mapping['status']) && is_int($mapping['status'])) {
            $status = $mapping['status'];
        }

        return new ProblemDetails(
            type: $type,
            title: $title,
            status: $status,
            detail: $detail,
            instance: $instance,
            errorCode: $errorCode,
            requestId: $requestId,
            violations: [],
            meta: $this->debugMeta($e)
        );
    }

    /**
     * @return Violation[]|null
     */
    private function tryMapValidation(Throwable $e): ?array
    {
        // Support Symfony ConstraintViolationListInterface if installed
        if (interface_exists(\Symfony\Component\Validator\ConstraintViolationListInterface::class)
            && $e instanceof \Symfony\Component\Validator\Exception\ValidationFailedException
        ) {
            $list = $e->getViolations();
            $out = [];
            foreach ($list as $violation) {
                $out[] = new Violation(
                    field: (string) $violation->getPropertyPath(),
                    message: (string) $violation->getMessage()
                );
            }
            return $out;
        }

        // If someone throws an object carrying violations, you can expand later (v0.2)
        return null;
    }

    private function safeDetail(Throwable $e, int $status): ?string
    {
        // For 4xx from HttpExceptionInterface, messages are usually safe
        if ($status >= 400 && $status < 500 && $e instanceof HttpExceptionInterface) {
            $msg = trim((string) $e->getMessage());
            return $msg !== '' ? $msg : null;
        }

        // Don’t leak internal messages for 5xx by default
        return $status >= 500 ? null : (trim((string) $e->getMessage()) ?: null);
    }

    /**
     * @return array<string, mixed>
     */
    private function debugMeta(Throwable $e): array
    {
        $meta = [];

        if ($this->includeExceptionClass) {
            $meta['exceptionClass'] = $e::class;
        }

        if ($this->includeTrace) {
            $meta['trace'] = explode("\n", $e->getTraceAsString());
        }

        return $meta;
    }

    private function fallbackTitle(int $status): string
    {
        return match ($status) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            415 => 'Unsupported Media Type',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            default => 'Error',
        };
    }
}
