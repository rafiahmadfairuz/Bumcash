<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanTagihanVariabel extends Model
{
    protected $table = 'permintaan_tagihan_variabel';
    protected $fillable = ['user_id', 'layanan_id', 'nomor_objek', 'status', 'nominal', 'tagihan_id', 'diproses_oleh', 'catatan'];
    protected function casts(): array { return ['nominal' => 'decimal:2']; }
    public function warga() { return $this->belongsTo(User::class, 'user_id'); }
    public function layanan() { return $this->belongsTo(Layanan::class); }
    public function tagihan() { return $this->belongsTo(Tagihan::class); }
}
