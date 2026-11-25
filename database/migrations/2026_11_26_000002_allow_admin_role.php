<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', ['PENGELOLA', 'ADMIN', 'WARGA'])->change();
        });
    }

    public function down(): void
    {
        // Keep existing administrator accounts intact when rolling back app code.
    }
};
