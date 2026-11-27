<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tagihan_utilitas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unit_usaha_id')->constrained('unit_usaha')->restrictOnDelete();
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();
            $table->string('kategori', 20);
            $table->string('nama_penyedia', 120);
            $table->string('nomor_objek', 100)->nullable();
            $table->string('periode', 30)->nullable();
            $table->string('referensi_tagihan', 100)->nullable()->unique();
            $table->decimal('nominal', 12, 2);
            $table->decimal('biaya_tambahan', 12, 2)->default(0);
            $table->date('tanggal_tagihan');
            $table->date('jatuh_tempo')->nullable();
            $table->string('status', 20)->default('TERCATAT');
            $table->string('referensi_pembayaran', 100)->nullable();
            $table->dateTime('waktu_pembayaran')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['unit_usaha_id', 'kategori', 'status']);
        });

        Schema::create('piutang_usaha', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unit_usaha_id')->constrained('unit_usaha')->restrictOnDelete();
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();
            $table->string('pihak', 150);
            $table->string('referensi_dokumen', 100)->nullable();
            $table->text('deskripsi');
            $table->date('tanggal_transaksi');
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('nominal', 12, 2);
            $table->decimal('saldo', 12, 2);
            $table->string('status', 20)->default('TERCATAT');
            $table->timestamps();
            $table->index(['unit_usaha_id', 'status', 'jatuh_tempo']);
            $table->unique(['unit_usaha_id', 'referensi_dokumen']);
        });

        Schema::create('utang_usaha', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unit_usaha_id')->constrained('unit_usaha')->restrictOnDelete();
            $table->foreignId('tagihan_utilitas_id')->nullable()->unique()->constrained('tagihan_utilitas')->restrictOnDelete();
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();
            $table->string('pihak', 150);
            $table->string('referensi_dokumen', 100)->nullable();
            $table->text('deskripsi');
            $table->date('tanggal_transaksi');
            $table->date('jatuh_tempo')->nullable();
            $table->decimal('nominal', 12, 2);
            $table->decimal('saldo', 12, 2);
            $table->string('status', 20)->default('TERCATAT');
            $table->timestamps();
            $table->index(['unit_usaha_id', 'status', 'jatuh_tempo']);
            $table->unique(['unit_usaha_id', 'referensi_dokumen']);
        });

        Schema::create('settlement_usaha', function (Blueprint $table): void {
            $table->id();
            $table->string('jenis_entitas', 20);
            $table->unsignedBigInteger('entitas_id');
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();
            $table->date('tanggal_settlement');
            $table->decimal('nominal', 12, 2);
            $table->string('metode', 50);
            $table->string('referensi', 100)->nullable()->unique();
            $table->foreignId('arus_kas_id')->nullable()->unique()->constrained('arus_kas')->restrictOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['jenis_entitas', 'entitas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_usaha');
        Schema::dropIfExists('utang_usaha');
        Schema::dropIfExists('piutang_usaha');
        Schema::dropIfExists('tagihan_utilitas');
    }
};
