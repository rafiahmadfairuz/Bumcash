<?php

namespace Tests\Feature\Payments;

use App\Models\PaymentTransaction;
use App\Models\Tagihan;
use App\Models\User;
use App\Payments\Exceptions\PaymentDisabled;
use App\Payments\DTO\VerifiedWebhookEvent;
use App\Payments\Gateways\MockPaymentGateway;
use App\Payments\PaymentManager;
use App\Payments\PaymentStatus;
use App\Payments\WebhookEventProcessor;
use App\Services\PaymentService;
use App\Services\PembukuanUsahaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_are_disabled_by_default(): void
    {
        config(['payment.enabled' => false]);
        $this->expectException(PaymentDisabled::class);
        app(PaymentManager::class)->gateway();
    }

    public function test_user_cannot_create_payment_for_another_users_invoice(): void
    {
        [$user, $other, $tagihan] = $this->invoiceFixture();
        config(['payment.enabled' => true, 'payment.provider' => 'mock', 'payment.mock.enabled' => true]);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        app(PaymentService::class)->create($tagihan->id, $other->id, 'attempt-1');
    }

    public function test_mock_payment_can_be_simulated_and_paid_is_terminal_except_refund(): void
    {
        config(['payment.enabled' => true, 'payment.provider' => 'mock', 'payment.mock.enabled' => true]);
        [$user, , $tagihan] = $this->invoiceFixture();
        $transaction = app(PaymentService::class)->create($tagihan->id, $user->id, 'attempt-1');
        $ref = $transaction->metadata['gateway_reference'];
        $gateway = app(MockPaymentGateway::class);
        $paid = app(PaymentService::class)->simulateMockTransition($transaction, PaymentStatus::Paid);
        $this->assertSame(PaymentStatus::Paid, $paid->status);
        $this->expectException(\DomainException::class);
        app(PaymentService::class)->transition($paid, PaymentStatus::Pending, verified: true);
    }

    public function test_duplicate_idempotency_key_returns_same_payment_and_amount_comes_from_invoice(): void
    {
        config(['payment.enabled' => true, 'payment.provider' => 'mock', 'payment.mock.enabled' => true]);
        [$user, , $tagihan] = $this->invoiceFixture();
        $service = app(PaymentService::class);
        $first = $service->create($tagihan->id, $user->id, 'same-key');
        $second = $service->create($tagihan->id, $user->id, 'same-key');
        $this->assertSame($first->id, $second->id);
        $this->assertSame('1250.00', $first->amount);
    }

    public function test_utility_bill_is_debt_until_partial_settlement_posts_cash(): void
    {
        [$user, , $tagihan] = $this->invoiceFixture();
        $service = app(PembukuanUsahaService::class);
        $bill = $service->catatUtilitas([
            'unit_usaha_id' => $tagihan->layanan->unit_usaha_id, 'kategori' => 'PLN', 'nama_penyedia' => 'PLN',
            'nominal' => '1000.00', 'biaya_tambahan' => '50.00', 'tanggal_tagihan' => '2026-11-01', 'jatuh_tempo' => '2026-11-30',
        ], $user->id);
        $utang = \App\Models\UtangUsaha::where('tagihan_utilitas_id', $bill->id)->firstOrFail();
        $this->assertEquals(0, \App\Models\ArusKas::sum('nominal'));

        $service->settlement('utang', $utang->id, ['nominal' => '400.00', 'tanggal_settlement' => '2026-11-20', 'metode' => 'TUNAI'], $user->id);
        $this->assertSame('650.00', $utang->fresh()->saldo);
        $this->assertEquals(400, \App\Models\ArusKas::sum('nominal'));
        $this->assertSame('DIBAYAR_SEBAGIAN', $bill->fresh()->status);
    }

    public function test_duplicate_verified_webhook_event_is_processed_once(): void
    {
        config(['payment.enabled' => true, 'payment.provider' => 'mock', 'payment.mock.enabled' => true]);
        [$user, , $invoice] = $this->invoiceFixture();
        $transaction = app(PaymentService::class)->create($invoice->id, $user->id, 'hook-key');
        $processor = app(WebhookEventProcessor::class);

        $event = new VerifiedWebhookEvent('mock', 'event-1', 'payment.paid', $transaction->internal_id, '1250.00', 'IDR', PaymentStatus::Paid);
        $first = $processor->processVerified($event);
        $duplicate = $processor->processVerified($event);

        $this->assertTrue($first);
        $this->assertFalse($duplicate);
        $this->assertSame(PaymentStatus::Paid, $transaction->fresh()->status);
        $this->assertDatabaseCount('payment_webhook_events', 1);
    }

    public function test_mock_simulation_is_blocked_outside_local_and_testing(): void
    {
        config(['payment.enabled' => true, 'payment.provider' => 'mock', 'payment.mock.enabled' => true]);
        [$user, , $invoice] = $this->invoiceFixture();
        $transaction = app(PaymentService::class)->create($invoice->id, $user->id, 'prod-key');
        $this->app['env'] = 'production';
        $this->expectException(\RuntimeException::class);
        app(PaymentService::class)->simulateMockTransition($transaction, PaymentStatus::Paid);
    }

    private function invoiceFixture(): array
    {
        $user = User::create(['nama_lengkap' => 'Warga Satu', 'username' => 'warga-satu', 'password' => 'secret', 'role' => 'WARGA']);
        $other = User::create(['nama_lengkap' => 'Warga Dua', 'username' => 'warga-dua', 'password' => 'secret', 'role' => 'WARGA']);
        $unit = \App\Models\UnitUsaha::create(['kode_unit' => 'UNIT-1', 'nama_unit' => 'Unit Air', 'penanggung_jawab' => 'PJ', 'status_aktif' => true]);
        $layanan = \App\Models\Layanan::create(['unit_usaha_id' => $unit->id, 'nama_layanan' => 'Air', 'satuan' => 'Bulan', 'tarif_dasar' => 1250]);
        $tagihan = Tagihan::create(['kode_tagihan' => 'INV-1', 'user_id' => $user->id, 'layanan_id' => $layanan->id, 'periode_bulan' => 1, 'periode_tahun' => 2026, 'rincian_pemakaian' => 'Air', 'nominal' => 1250, 'jatuh_tempo' => '2026-01-20', 'status' => 'BELUM_DIBAYAR']);
        return [$user, $other, $tagihan];
    }
}
