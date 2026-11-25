<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ArusKas extends Model { protected $table='arus_kas'; protected $fillable=['kode_kas','unit_usaha_id','tanggal_transaksi','jenis','sumber','kategori_akun','keterangan','nominal','transaksi_pembayaran_id','dicatat_oleh']; protected function casts(): array {return ['tanggal_transaksi'=>'date','nominal'=>'decimal:2'];} public function unitUsaha(){return $this->belongsTo(UnitUsaha::class);} public function transaksiPembayaran(){return $this->belongsTo(TransaksiPembayaran::class);} public function pencatat(){return $this->belongsTo(User::class,'dicatat_oleh');} }
