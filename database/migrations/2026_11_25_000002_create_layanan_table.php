<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('layanan', function (Blueprint $table) { $table->id(); $table->foreignId('unit_usaha_id')->constrained('unit_usaha'); $table->string('nama_layanan',100); $table->string('satuan',30); $table->decimal('tarif_dasar',12,2); $table->text('deskripsi')->nullable(); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('layanan'); } };
