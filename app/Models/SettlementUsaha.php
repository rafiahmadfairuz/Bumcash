<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementUsaha extends Model
{
    protected $table = 'settlement_usaha';
    protected $fillable = ['jenis_entitas', 'entitas_id', 'dicatat_oleh', 'tanggal_settlement', 'nominal', 'metode', 'referensi', 'arus_kas_id', 'catatan'];
    protected function casts(): array { return ['tanggal_settlement' => 'date', 'nominal' => 'decimal:2']; }
    public function arusKas() { return $this->belongsTo(ArusKas::class); }
}
