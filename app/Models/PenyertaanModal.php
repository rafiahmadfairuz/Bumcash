<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PenyertaanModal extends Model { protected $table='penyertaan_modal'; protected $fillable=['unit_usaha_id','tanggal_terima','sumber_modal','nomor_perdes','nominal','keterangan']; protected function casts(): array {return ['tanggal_terima'=>'date','nominal'=>'decimal:2'];} public function unitUsaha(){return $this->belongsTo(UnitUsaha::class);} }
