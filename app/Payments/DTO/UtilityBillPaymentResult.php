<?php

namespace App\Payments\DTO;

use App\Payments\PaymentStatus;

final readonly class UtilityBillPaymentResult
{
    public function __construct(
        public string $providerBillReference,
        public PaymentStatus $status,
        public ?string $providerPaymentReference = null,
        public ?\DateTimeImmutable $paidAt = null,
        public array $safeMetadata = [],
    ) {}
}
