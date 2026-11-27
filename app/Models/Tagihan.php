<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tagihan extends Model { protected $table='tagihan'; protected $fillable=['kode_tagihan','user_id','layanan_id','periode_bulan','periode_tahun','rincian_pemakaian','nominal','jatuh_tempo','status']; protected function casts(): array {return ['nominal'=>'decimal:2','jatuh_tempo'=>'date'];} public function warga(){return $this->belongsTo(User::class,'user_id');} public function layanan(){return $this->belongsTo(Layanan::class);} public function transaksiPembayaran(){return $this->hasMany(TransaksiPembayaran::class);} public function paymentTransactions(){return $this->hasMany(PaymentTransaction::class);} }
