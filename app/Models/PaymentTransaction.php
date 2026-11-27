<?php

namespace App\Models;

use App\Payments\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['internal_id', 'tagihan_id', 'user_id', 'provider', 'provider_transaction_id', 'amount', 'currency', 'status', 'payment_method', 'paid_at', 'expires_at', 'idempotency_key', 'metadata'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'expires_at' => 'datetime', 'metadata' => 'array', 'status' => PaymentStatus::class];
    }

    public function tagihan() { return $this->belongsTo(Tagihan::class); }
    public function warga() { return $this->belongsTo(User::class, 'user_id'); }
}
