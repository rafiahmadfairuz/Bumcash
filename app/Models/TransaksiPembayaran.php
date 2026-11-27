<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPembayaran extends Model
{
    protected $table = 'transaksi_pembayaran';

    protected $fillable = [
        'kode_transaksi', 'tagihan_id', 'user_id', 'nominal_dibayar', 'metode_pembayaran',
        'status', 'catatan_pengelola', 'waktu_bayar', 'waktu_konfirmasi', 'dicatat_oleh', 'referensi_admin',
    ];

    protected function casts(): array
    {
        return [
            'nominal_dibayar' => 'decimal:2',
            'waktu_bayar' => 'datetime',
            'waktu_konfirmasi' => 'datetime',
        ];
    }

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function warga()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function arusKas()
    {
        return $this->hasOne(ArusKas::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
