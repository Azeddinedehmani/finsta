<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('doctor_name');
            $table->string('doctor_phone')->nullable();
            $table->string('doctor_speciality')->nullable();
            $table->date('prescription_date');
            $table->date('expiry_date');
            $table->enum('status', ['pending', 'partially_delivered', 'completed', 'expired'])->default('pending');
            $table->text('medical_notes')->nullable();
            $table->text('pharmacist_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('delivered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};