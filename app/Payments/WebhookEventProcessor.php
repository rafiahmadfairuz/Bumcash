<?php

namespace App\Payments;

use App\Models\PaymentTransaction;
use App\Payments\DTO\VerifiedWebhookEvent;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

class WebhookEventProcessor
{
    public function __construct(private PaymentService $payments) {}

    public function processVerified(VerifiedWebhookEvent $event): bool
    {
        return DB::transaction(function () use ($event): bool {
            $inserted = DB::table('payment_webhook_events')->insertOrIgnore([
                'provider' => $event->provider, 'event_key' => $event->eventId, 'event_type' => $event->eventType,
                'payment_reference' => $event->internalId, 'created_at' => now(), 'updated_at' => now(),
            ]);
            if (! $inserted) return false;

            $transaction = PaymentTransaction::where('internal_id', $event->internalId)->lockForUpdate()->firstOrFail();
            if ($transaction->provider !== $event->provider
                || $transaction->currency !== strtoupper($event->currency)
                || $this->cents((string) $transaction->amount) !== $this->cents($event->amount)
                || ($transaction->provider_transaction_id && $event->providerTransactionId && $transaction->provider_transaction_id !== $event->providerTransactionId)) {
                throw new \DomainException('Verified webhook does not match the internal transaction.');
            }
            if ($event->providerTransactionId && ! $transaction->provider_transaction_id) {
                $transaction->provider_transaction_id = $event->providerTransactionId;
                $transaction->save();
            }
            $this->payments->transition($transaction, $event->status, verified: true);
            DB::table('payment_webhook_events')->where(['provider' => $event->provider, 'event_key' => $event->eventId])->update(['processed_at' => now(), 'updated_at' => now()]);
            return true;
        });
    }

    private function cents(string $value): int
    {
        if (! preg_match('/\A(\d+)(?:\.(\d{1,2}))?\z/', $value, $parts)) throw new \DomainException('Invalid webhook amount.');
        return ((int) $parts[1] * 100) + (int) str_pad($parts[2] ?? '0', 2, '0');
    }
}
