<?php

namespace App\Payments\Contracts;

use App\Payments\DTO\CreatePaymentRequest;
use App\Payments\DTO\GatewayPayment;

interface PaymentGateway
{
    public function create(CreatePaymentRequest $request): GatewayPayment;

    public function retrieve(string $reference): GatewayPayment;

    public function cancel(string $reference): ?GatewayPayment;

    public function refund(string $reference, ?string $amount = null): ?GatewayPayment;
}
