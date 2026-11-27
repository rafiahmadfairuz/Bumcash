<?php

namespace App\Payments\Gateways;

use App\Payments\Contracts\PaymentGateway;
use App\Payments\DTO\CreatePaymentRequest;
use App\Payments\DTO\GatewayPayment;
use App\Payments\PaymentStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGateway
{
    public function create(CreatePaymentRequest $request): GatewayPayment
    {
        $result = new GatewayPayment('mock_'.Str::random(32), PaymentStatus::Pending, expiresAt: new \DateTimeImmutable('+30 minutes'), metadata: ['reference' => $request->reference]);
        Cache::put($this->key($result->reference), $result, now()->addMinutes(35));
        return $result;
    }

    public function retrieve(string $reference): GatewayPayment
    {
        return Cache::get($this->key($reference)) ?? throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Mock payment not found.');
    }

    public function cancel(string $reference): ?GatewayPayment
    {
        return $this->transition($reference, PaymentStatus::Cancelled);
    }

    public function refund(string $reference, ?string $amount = null): ?GatewayPayment
    {
        return $this->transition($reference, PaymentStatus::Refunded);
    }

    public function simulate(string $reference, PaymentStatus $status): GatewayPayment
    {
        if (! in_array(app()->environment(), ['local', 'testing'], true) || ! config('payment.mock.enabled')) {
            throw new \RuntimeException('Mock payments are only enabled in local/testing with PAYMENT_MOCK_ENABLED=true.');
        }

        return $this->transition($reference, $status);
    }

    private function transition(string $reference, PaymentStatus $status): GatewayPayment
    {
        $payment = $this->retrieve($reference);
        if (! $payment->status->canTransitionTo($status)) {
            throw new \DomainException('Invalid mock payment status transition.');
        }
        $updated = new GatewayPayment($payment->reference, $status, $payment->checkoutUrl, $payment->providerTransactionId, $payment->expiresAt, $payment->metadata);
        Cache::put($this->key($reference), $updated, now()->addMinutes(35));
        return $updated;
    }

    private function key(string $reference): string { return 'payment:mock:'.hash('sha256', $reference); }
}
