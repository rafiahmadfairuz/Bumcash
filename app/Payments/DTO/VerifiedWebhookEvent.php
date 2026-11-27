<?php

namespace App\Payments\DTO;

use App\Payments\PaymentStatus;

final readonly class VerifiedWebhookEvent
{
    public function __construct(
        public string $provider,
        public string $eventId,
        public string $eventType,
        public string $internalId,
        public string $amount,
        public string $currency,
        public PaymentStatus $status,
        public ?string $providerTransactionId = null,
    ) {}
}
