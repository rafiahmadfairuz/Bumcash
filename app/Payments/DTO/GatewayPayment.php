<?php

namespace App\Payments\DTO;

use App\Payments\PaymentStatus;

final readonly class GatewayPayment
{
    public function __construct(
        public string $reference,
        public PaymentStatus $status,
        public ?string $checkoutUrl = null,
        public ?string $providerTransactionId = null,
        public ?\DateTimeImmutable $expiresAt = null,
        public array $metadata = [],
    ) {}
}
