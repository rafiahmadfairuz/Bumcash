<?php

namespace App\Payments\Contracts;

use App\Payments\DTO\UtilityBillPaymentResult;
use App\Payments\DTO\UtilityBillQuote;

/** Provider-specific adapter for bill inquiry and provider settlement.
 * This is separate from PaymentGateway, which collects funds from the resident.
 */
interface UtilityBillProvider
{
    public function inquire(string $category, string $customerReference): UtilityBillQuote;

    public function pay(UtilityBillQuote $quote, string $idempotencyKey): UtilityBillPaymentResult;
}
