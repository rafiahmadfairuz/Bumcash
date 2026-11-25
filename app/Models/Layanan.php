<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Layanan extends Model { protected $table='layanan'; protected $fillable=['unit_usaha_id','nama_layanan','satuan','tarif_dasar','deskripsi']; protected function casts(): array {return ['tarif_dasar'=>'decimal:2'];} public function unitUsaha(){return $this->belongsTo(UnitUsaha::class);} public function tagihan(){return $this->hasMany(Tagihan::class);} }
