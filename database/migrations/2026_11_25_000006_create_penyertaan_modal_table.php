<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('penyertaan_modal', function (Blueprint $table) { $table->id(); $table->date('tanggal_terima'); $table->string('sumber_modal',100); $table->string('nomor_perdes',100); $table->decimal('nominal',12,2); $table->text('keterangan'); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('penyertaan_modal'); } };
