<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\EventSubscriber;

use Faez84\ProblemDetailsBundle\Mapper\ExceptionToProblemDetailsMapper;
use Faez84\ProblemDetailsBundle\Normalizer\ProblemDetailsNormalizer;
use Faez84\ProblemDetailsBundle\Request\RequestIdProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class ProblemDetailsExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @param array{path_prefixes:string[],accept_contains:string[],content_type_contains:string[]} $applyWhen
     */
    public function __construct(
        private ExceptionToProblemDetailsMapper $mapper,
        private ProblemDetailsNormalizer $normalizer,
        private RequestIdProvider $requestIdProvider,
        private bool $enabled,
        private array $applyWhen,
        private bool $setResponseRequestIdHeader,
    ) {}

    public static function getSubscribedEvents(): array
    {
        // priority > default Symfony error listener
        return [KernelEvents::EXCEPTION => ['onKernelException', 50]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->shouldApply($request->getPathInfo(), $request->headers->get('Accept'), $request->headers->get('Content-Type'))) {
            return;
        }

        $e = $event->getThrowable();

        // if a response is already set, usually better to not override
        // but you can change this behavior later (v0.2)
        $pd = $this->mapper->map($e, $request);
        $payload = $this->normalizer->normalize($pd);

        $response = new JsonResponse(
            $payload,
            $pd->status,
            ['Content-Type' => 'application/problem+json']
        );

        if ($this->setResponseRequestIdHeader) {
            $response->headers->set($this->requestIdProvider->headerName(), (string) ($payload['requestId'] ?? ''));
        }

        $event->setResponse($response);
    }

    private function shouldApply(string $path, ?string $accept, ?string $contentType): bool
    {
        foreach ($this->applyWhen['path_prefixes'] ?? ['/api'] as $prefix) {
            if ($prefix !== '' && str_starts_with($path, $prefix)) {
                return true;
            }
        }

        $accept = $accept ?? '';
        foreach ($this->applyWhen['accept_contains'] ?? ['application/json'] as $needle) {
            if ($needle !== '' && stripos($accept, $needle) !== false) {
                return true;
            }
        }

        $contentType = $contentType ?? '';
        foreach ($this->applyWhen['content_type_contains'] ?? ['application/json'] as $needle) {
            if ($needle !== '' && stripos($contentType, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
