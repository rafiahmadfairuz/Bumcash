<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Layanan extends Model { protected $table='layanan'; protected $fillable=['unit_usaha_id','nama_layanan','satuan','tarif_dasar','deskripsi','status_aktif','jenis_layanan','tampil_di_katalog']; protected function casts(): array {return ['tarif_dasar'=>'decimal:2','status_aktif'=>'boolean','tampil_di_katalog'=>'boolean'];} public function unitUsaha(){return $this->belongsTo(UnitUsaha::class);} public function tagihan(){return $this->hasMany(Tagihan::class);} }
