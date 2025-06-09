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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // stock_alert, expiry_alert, sale_created, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable(); // URL to redirect when clicked
            $table->enum('priority', ['low', 'normal', 'medium', 'high'])->default('normal');
            $table->timestamp('expires_at')->nullable(); // When the notification expires
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'created_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};