<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penyertaan_modal', function (Blueprint $table) {
            $table->foreignId('unit_usaha_id')->nullable()->after('id')->constrained('unit_usaha');
        });
    }

    public function down(): void
    {
        Schema::table('penyertaan_modal', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_usaha_id');
        });
    }
};
