<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permintaan_tagihan_variabel', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->restrictOnDelete();
            $table->string('nomor_objek', 100);
            $table->string('status', 24)->default('MENUNGGU_PENGECEKAN')->index();
            $table->decimal('nominal', 12, 2)->nullable();
            $table->foreignId('tagihan_id')->nullable()->constrained('tagihan')->restrictOnDelete();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('permintaan_tagihan_variabel'); }
};
