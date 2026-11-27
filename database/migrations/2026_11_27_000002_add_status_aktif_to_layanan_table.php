<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('layanan', 'status_aktif')) {
            Schema::table('layanan', function (Blueprint $table): void {
                $table->boolean('status_aktif')->default(true)->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('layanan', 'status_aktif')) {
            Schema::table('layanan', fn (Blueprint $table) => $table->dropColumn('status_aktif'));
        }
    }
};
