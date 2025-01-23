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
        Schema::create('rental_electricity_bill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_agreement_id')->nullable()->constrained('rental_agreement')->onDelete('cascade');
            $table->string('bill_date', 191)->nullable();
            $table->unsignedInteger('month')->nullable();
            $table->unsignedInteger('year')->nullable();
            $table->double('amount', 8, 2)->nullable();
            $table->unsignedBigInteger('previous_meter_reading')->nullable();
            $table->unsignedBigInteger('current_meter_reading')->nullable();
            $table->unsignedInteger('previous_unit_consumed')->nullable();
            $table->unsignedInteger('unit_consumed')->nullable();
            $table->unsignedInteger('unit_difference')->nullable();
            $table->double('difference_in_percent', 8, 2)->nullable();
            $table->string('meter_reading_img', 191)->nullable();
            $table->string('receipt_upload_image', 191)->nullable();
            $table->enum('status_req', ['cancelled', 'paid', 'pending'])->default('pending');
            $table->unsignedInteger('billing_month')->nullable();
            $table->unsignedInteger('billing_year')->nullable();
            $table->double('extra_charges', 8, 2)->nullable();
            $table->string('remarks', 191)->nullable();
            $table->enum('payment_type', ['Vianet', 'Landlord', 'Lease'])->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_bill');
    }
};
