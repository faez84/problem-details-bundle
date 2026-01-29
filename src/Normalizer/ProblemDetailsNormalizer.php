<?php

declare(strict_types=1);

namespace Faez84\ProblemDetailsBundle\Normalizer;

use Faez84\ProblemDetailsBundle\Model\ProblemDetails;

final class ProblemDetailsNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public function normalize(ProblemDetails $pd): array
    {
        $data = [
            'type' => $pd->type,
            'title' => $pd->title,
            'status' => $pd->status,
            'detail' => $pd->detail,
            'instance' => $pd->instance,
            'errorCode' => $pd->errorCode,
            'requestId' => $pd->requestId,
            'violations' => array_map(
                fn($v) => ['field' => $v->field, 'message' => $v->message],
                $pd->violations
            ),
        ];

        foreach ($pd->meta as $k => $v) {
            if (!array_key_exists($k, $data)) {
                $data[$k] = $v;
            }
        }

        return array_filter($data, static function ($value) {
            if ($value === null) return false;
            if (is_array($value) && $value === []) return false;
            return true;
        });
    }
}
