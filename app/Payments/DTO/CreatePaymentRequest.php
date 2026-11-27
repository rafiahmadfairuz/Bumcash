<?php

namespace App\Payments\DTO;

final readonly class CreatePaymentRequest
{
    public function __construct(
        public string $reference,
        public string $amount,
        public string $currency,
        public array $metadata = [],
    ) {}
}
