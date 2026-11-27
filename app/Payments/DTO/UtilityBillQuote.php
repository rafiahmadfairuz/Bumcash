<?php

namespace App\Payments\DTO;

final readonly class UtilityBillQuote
{
    public function __construct(
        public string $provider,
        public string $category,
        public string $customerReference,
        public string $providerBillReference,
        public string $principalAmount,
        public string $serviceFee,
        public string $currency,
        public ?\DateTimeImmutable $expiresAt = null,
        public array $safeMetadata = [],
    ) {}

    public function totalAmount(): string
    {
        [$principalWhole, $principalFraction] = $this->parts($this->principalAmount);
        [$feeWhole, $feeFraction] = $this->parts($this->serviceFee);
        $cents = ($principalWhole * 100 + $principalFraction) + ($feeWhole * 100 + $feeFraction);
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    private function parts(string $amount): array
    {
        if (! preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', $amount, $matches)) {
            throw new \InvalidArgumentException('Amount must be a non-negative decimal with at most two fractional digits.');
        }
        return [(int) $matches[1], (int) str_pad($matches[2] ?? '0', 2, '0')];
    }
}
