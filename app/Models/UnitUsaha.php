<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UnitUsaha extends Model { protected $table='unit_usaha'; protected $fillable=['kode_unit','nama_unit','penanggung_jawab','deskripsi','status_aktif']; protected function casts(): array {return ['status_aktif'=>'boolean'];} public function layanan(){return $this->hasMany(Layanan::class);} public function arusKas(){return $this->hasMany(ArusKas::class);} public function penyertaanModal(){return $this->hasMany(PenyertaanModal::class);} public function asetInventaris(){return $this->hasMany(AsetInventaris::class);} }
