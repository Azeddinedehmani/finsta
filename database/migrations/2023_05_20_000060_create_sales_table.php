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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('client_name_at_deletion')->nullable();
            $table->json('deleted_client_data')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('sale_number')->unique();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'insurance', 'other'])->default('cash');
            $table->enum('payment_status', ['paid', 'pending', 'failed'])->default('paid');
            $table->boolean('has_prescription')->default(false);
            $table->string('prescription_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sale_date');
            $table->timestamps();

            // Indexes pour améliorer les performances
            $table->index(['client_id', 'sale_date']);
            $table->index(['payment_status', 'sale_date']);
            $table->index('sale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};