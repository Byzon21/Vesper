<?php

declare(strict_types=1);

namespace App\Domain\Analytics;

/**
 * Événement d'analytics UX (Value Object immuable).
 * Aucune donnée personnelle (PII) : uniquement des signaux comportementaux.
 */
final readonly class UxEvent
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $sessionId,
        public UxEventType $type,
        public string $page,
        public ?string $component,
        public ?string $elementId,
        public array $metadata,
        public \DateTimeImmutable $clientTs,
    ) {
    }
}
