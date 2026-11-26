<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('unit_usaha', function (Blueprint $table) { $table->id(); $table->string('kode_unit',20)->unique(); $table->string('nama_unit',100); $table->string('penanggung_jawab',100); $table->text('deskripsi')->nullable(); $table->boolean('status_aktif')->default(true); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('unit_usaha'); } };
