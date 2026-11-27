<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtangUsaha extends Model
{
    protected $table = 'utang_usaha';
    protected $fillable = ['unit_usaha_id', 'tagihan_utilitas_id', 'dicatat_oleh', 'pihak', 'referensi_dokumen', 'deskripsi', 'tanggal_transaksi', 'jatuh_tempo', 'nominal', 'saldo', 'status'];
    protected function casts(): array { return ['tanggal_transaksi' => 'date', 'jatuh_tempo' => 'date', 'nominal' => 'decimal:2', 'saldo' => 'decimal:2']; }
    public function unitUsaha() { return $this->belongsTo(UnitUsaha::class); }
    public function tagihanUtilitas() { return $this->belongsTo(TagihanUtilitas::class); }
}
