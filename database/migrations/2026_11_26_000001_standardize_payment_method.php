<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transaksi_pembayaran') || ! Schema::hasColumn('transaksi_pembayaran', 'metode_simulasi')) {
            return;
        }

        Schema::table('transaksi_pembayaran', function (Blueprint $table): void {
            $table->string('metode_pembayaran', 32)->nullable();
        });

        DB::table('transaksi_pembayaran')->update([
            'metode_pembayaran' => DB::raw("CASE WHEN metode_simulasi = 'SIMULASI_TRANSFER_DESA' THEN 'TRANSFER_BANK_DESA' ELSE 'TUNAI_LOKET' END"),
        ]);

        Schema::table('transaksi_pembayaran', function (Blueprint $table): void {
            $table->dropColumn('metode_simulasi');
            if (Schema::hasColumn('transaksi_pembayaran', 'is_simulasi')) {
                $table->dropColumn('is_simulasi');
            }
        });
    }

    public function down(): void
    {
        // Payment method values remain in the normalized production format.
    }
};
