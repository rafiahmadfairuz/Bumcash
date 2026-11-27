<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transaksi_pembayaran', function (Blueprint $table): void {
            $table->foreignId('dicatat_oleh')->nullable()->after('user_id')->constrained('users')->restrictOnDelete();
            $table->string('referensi_admin', 100)->nullable()->unique()->after('kode_transaksi');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_pembayaran', function (Blueprint $table): void {
            $table->dropUnique(['referensi_admin']);
            $table->dropConstrainedForeignId('dicatat_oleh');
            $table->dropColumn('referensi_admin');
        });
    }
};
