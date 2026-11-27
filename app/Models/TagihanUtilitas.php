<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanUtilitas extends Model
{
    protected $table = 'tagihan_utilitas';
    protected $fillable = ['unit_usaha_id', 'dicatat_oleh', 'kategori', 'nama_penyedia', 'nomor_objek', 'periode', 'referensi_tagihan', 'nominal', 'biaya_tambahan', 'tanggal_tagihan', 'jatuh_tempo', 'status', 'referensi_pembayaran', 'waktu_pembayaran', 'catatan'];
    protected function casts(): array { return ['nominal' => 'decimal:2', 'biaya_tambahan' => 'decimal:2', 'tanggal_tagihan' => 'date', 'jatuh_tempo' => 'date', 'waktu_pembayaran' => 'datetime']; }
    public function unitUsaha() { return $this->belongsTo(UnitUsaha::class); }
    public function pencatat() { return $this->belongsTo(User::class, 'dicatat_oleh'); }
    public function utangUsaha() { return $this->hasOne(UtangUsaha::class); }
}
