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
        Schema::create('digital_service_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('service_type', 20);
            $table->string('provider', 50)->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('customer_id', 50)->nullable();
            $table->string('meter_number', 50)->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->string('provider_reference', 100)->nullable();
            $table->string('token_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('service_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_service_details');
    }
};
