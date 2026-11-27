<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('langganan_layanan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('layanan_id')->constrained('layanan')->restrictOnDelete();
            $table->string('status', 20)->default('AKTIF');
            $table->timestamp('disetujui_pada');
            $table->timestamp('dihentikan_pada')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'layanan_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('langganan_layanan'); }
};
