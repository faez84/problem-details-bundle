<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ProblemDetailsExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * @param array{
     *   path_prefixes?: string[],
     *   accept_contains?: string[],
     *   content_type_contains?: string[]
     * } $applyWhen
     */
    public function __construct(
        private array $applyWhen = [
            'path_prefixes' => ['/api'],
            'accept_contains' => ['application/json', 'application/problem+json'],
            'content_type_contains' => ['application/json', 'application/ld+json'],
        ],
        private bool $expose500Message = false,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', 50]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->shouldApply(
            $request->getPathInfo(),
            $request->headers->get('Accept'),
            $request->headers->get('Content-Type')
        )) {
            return;
        }

        $e = $event->getThrowable();
        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        $title = $this->titleFor($e, $status);

        $response = new JsonResponse(
            [
                'type' => 'about:blank',
                'title' => $title,
                'status' => $status,
            ],
            $status,
            ['Content-Type' => 'application/problem+json']
        );

        $event->setResponse($response);
    }

    private function titleFor(Throwable $e, int $status): string
    {
        if ($status >= 500 && !$this->expose500Message) {
            return 'Internal Server Error';
        }

        $msg = trim($e->getMessage());

        if ($msg === '') {
            return $status >= 500 ? 'Internal Server Error' : 'Error';
        }

        return $msg;
    }

    private function shouldApply(string $path, ?string $accept, ?string $contentType): bool
    {
        foreach (($this->applyWhen['path_prefixes'] ?? ['/api']) as $prefix) {
            if ($prefix !== '' && str_starts_with($path, $prefix)) {
                return true;
            }
        }

        $accept = $accept ?? '';
        foreach (($this->applyWhen['accept_contains'] ?? ['application/json']) as $needle) {
            if ($needle !== '' && stripos($accept, $needle) !== false) {
                return true;
            }
        }

        $contentType = $contentType ?? '';
        foreach (($this->applyWhen['content_type_contains'] ?? ['application/json']) as $needle) {
            if ($needle !== '' && stripos($contentType, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
