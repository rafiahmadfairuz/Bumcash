<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AsetInventaris extends Model { protected $table='aset_inventaris'; protected $fillable=['kode_aset','unit_usaha_id','nama_aset','tanggal_perolehan','nilai_perolehan','kondisi']; protected function casts(): array {return ['tanggal_perolehan'=>'date','nilai_perolehan'=>'decimal:2'];} public function unitUsaha(){return $this->belongsTo(UnitUsaha::class);} }
