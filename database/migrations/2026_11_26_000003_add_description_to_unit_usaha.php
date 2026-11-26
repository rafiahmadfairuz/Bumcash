<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('unit_usaha') && ! Schema::hasColumn('unit_usaha', 'deskripsi')) {
            Schema::table('unit_usaha', function (Blueprint $table): void {
                $table->text('deskripsi')->nullable()->after('penanggung_jawab');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('unit_usaha') && Schema::hasColumn('unit_usaha', 'deskripsi')) {
            Schema::table('unit_usaha', function (Blueprint $table): void {
                $table->dropColumn('deskripsi');
            });
        }
    }
};
