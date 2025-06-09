<?php
// database/migrations/2024_01_XX_make_supplier_nullable_in_products.php
// Create this migration to make supplier_id nullable in products table

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
        Schema::table('products', function (Blueprint $table) {
            // Make supplier_id nullable so pharmacists can add products without suppliers
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert back to non-nullable (be careful with existing data)
            $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
        });
    }
};
