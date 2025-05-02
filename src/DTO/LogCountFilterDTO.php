<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\Request;

class LogCountFilterDTO
{
    public function __construct(
        public readonly ?array         $serviceNames = null,
        public ?\DateTimeInterface     $startDate = null,
        public ?\DateTimeInterface     $endDate = null,
        public readonly array|int|null $statusCode = null,
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        $serviceNames = self::normalizeToArray($request->query->get('serviceNames'));
        $statusCode = self::normalizeToArrayOrInt($request->query->get('statusCode'));

        $startDate = $request->query->get('startDate') ? new \DateTime($request->query->get('startDate')) : null;
        $endDate = $request->query->get('endDate') ? new \DateTime($request->query->get('endDate')) : null;

        return new self(
            $serviceNames,
            $startDate,
            $endDate,
            $statusCode
        );
    }

    private static function normalizeToArray(string|array|null $value): array
    {
        if (is_array($value)) {
            return array_map('trim', $value);
        }

        if (is_string($value)) {
            return array_filter(array_map('trim', explode(',', $value)));
        }

        return [];
    }

    private static function normalizeToArrayOrInt(string|array|null $value): array|int|null
    {
        if (is_array($value)) {
            return array_map('intval', $value);
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $parts = array_filter(array_map('trim', explode(',', $value)));
            return count($parts) > 1 ? array_map('intval', $parts) : (int) $parts[0];
        }

        return null;
    }
}