<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Tagihan;
use App\Payments\DTO\CreatePaymentRequest;
use App\Payments\PaymentManager;
use App\Payments\PaymentStatus;
use App\Payments\Gateways\MockPaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private PaymentManager $payments) {}

    public function create(int $tagihanId, int $userId, string $idempotencyKey): PaymentTransaction
    {
        if (! config('payment.enabled')) throw new \App\Payments\Exceptions\PaymentDisabled('Pembayaran online belum tersedia.');
        $gateway = $this->payments->gateway();
        [$transaction, $created] = DB::transaction(function () use ($tagihanId, $userId, $idempotencyKey): array {
            $tagihan = Tagihan::query()->whereKey($tagihanId)->where('user_id', $userId)->lockForUpdate()->firstOrFail();
            $existing = PaymentTransaction::where('tagihan_id', $tagihan->id)->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) return [$existing, false];
            if ($tagihan->status !== 'BELUM_DIBAYAR') throw ValidationException::withMessages(['payment' => 'Tagihan bukan dalam status yang dapat dibayar online.']);
            if (PaymentTransaction::where('tagihan_id', $tagihan->id)->where('status', PaymentStatus::Pending->value)->exists()) {
                throw ValidationException::withMessages(['payment' => 'Tagihan sudah memiliki transaksi online yang menunggu.']);
            }

            return [PaymentTransaction::create([
                'internal_id' => (string) Str::uuid(), 'tagihan_id' => $tagihan->id, 'user_id' => $userId,
                'provider' => config('payment.provider'),
                'amount' => $tagihan->nominal, 'currency' => config('payment.currency', 'IDR'),
                'status' => PaymentStatus::Pending, 'idempotency_key' => $idempotencyKey,
            ]), true];
        });

        if (! $created) return $transaction;

        try {
            $gatewayPayment = $gateway->create(new CreatePaymentRequest($transaction->internal_id, (string) $transaction->amount, $transaction->currency));
            $transaction->forceFill([
                'provider_transaction_id' => $gatewayPayment->providerTransactionId,
                'expires_at' => $gatewayPayment->expiresAt,
                'metadata' => ['gateway_reference' => $gatewayPayment->reference],
            ])->save();
            return $transaction->refresh();
        } catch (\Throwable) {
            $this->transition($transaction, PaymentStatus::Failed);
            throw new \RuntimeException('Pembayaran belum dapat diinisialisasi. Coba lagi nanti.');
        }
    }

    public function transition(PaymentTransaction $transaction, PaymentStatus $next, bool $verified = false): PaymentTransaction
    {
        if ($next === PaymentStatus::Paid && ! $verified) throw new \DomainException('Paid status requires a verified gateway result.');

        return DB::transaction(function () use ($transaction, $next): PaymentTransaction {
            $locked = PaymentTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === $next) return $locked;
            if (! $locked->status->canTransitionTo($next)) throw new \DomainException('Invalid payment status transition.');
            $locked->status = $next;
            if ($next === PaymentStatus::Paid) $locked->paid_at = now();
            $locked->save();
            return $locked->refresh();
        });
    }

    public function simulateMockTransition(PaymentTransaction $transaction, PaymentStatus $next): PaymentTransaction
    {
        if (! in_array(app()->environment(), ['local', 'testing'], true) || ! config('payment.mock.enabled')) {
            throw new \RuntimeException('Mock payments are only available in local/testing when explicitly enabled.');
        }

        $reference = $transaction->metadata['gateway_reference'] ?? null;
        if (! is_string($reference) || $reference === '') throw new \DomainException('Mock gateway reference is missing.');

        $result = app(MockPaymentGateway::class)->simulate($reference, $next);
        return $this->transition($transaction, $result->status, verified: true);
    }
}
