<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiutangUsaha extends Model
{
    protected $table = 'piutang_usaha';
    protected $fillable = ['unit_usaha_id', 'dicatat_oleh', 'pihak', 'referensi_dokumen', 'deskripsi', 'tanggal_transaksi', 'jatuh_tempo', 'nominal', 'saldo', 'status'];
    protected function casts(): array { return ['tanggal_transaksi' => 'date', 'jatuh_tempo' => 'date', 'nominal' => 'decimal:2', 'saldo' => 'decimal:2']; }
    public function unitUsaha() { return $this->belongsTo(UnitUsaha::class); }
}
