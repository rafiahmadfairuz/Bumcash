<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('layanan', function (Blueprint $table): void {
            $table->string('jenis_layanan', 20)->default('BULANAN');
            $table->boolean('tampil_di_katalog')->default(false)->index();
        });
        Schema::table('tagihan', function (Blueprint $table): void {
            $table->dropUnique('tagihan_user_id_layanan_id_periode_bulan_periode_tahun_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table): void {
            $table->unique(['user_id', 'layanan_id', 'periode_bulan', 'periode_tahun']);
        });
        Schema::table('layanan', function (Blueprint $table): void {
            $table->dropColumn(['jenis_layanan', 'tampil_di_katalog']);
        });
    }
};
