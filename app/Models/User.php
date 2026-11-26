<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\BumcashResetPassword;
use App\Notifications\BumcashVerifyEmail;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;
    protected $fillable=['kode_pelanggan','nama_lengkap','username','email','password','role','alamat_dusun','no_telepon'];
    protected $hidden=['password','remember_token'];
    protected function casts(): array {return ['password'=>'hashed'];}
    public function routeNotificationForMail(): ?string { return $this->email; }
    public function sendEmailVerificationNotification(): void { $this->notify(new BumcashVerifyEmail()); }
    public function sendPasswordResetNotification($token): void { $this->notify(new BumcashResetPassword($token)); }
    public function hasVerifiedEmail(): bool { return blank($this->email) || parent::hasVerifiedEmail(); }
    public function tagihan(){return $this->hasMany(Tagihan::class);}
    public function transaksiPembayaran(){return $this->hasMany(TransaksiPembayaran::class);}
    public function arusKasDicatat(){return $this->hasMany(ArusKas::class,'dicatat_oleh');}
}
