<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('password_reset_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('password_reset_codes', 'attempts')) {
                $table->integer('attempts')->default(0)->after('used');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_codes', function (Blueprint $table) {
            if (Schema::hasColumn('password_reset_codes', 'attempts')) {
                $table->dropColumn('attempts');
            }
        });
    }
};