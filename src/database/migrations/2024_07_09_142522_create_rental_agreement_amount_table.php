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
        Schema::create('rental_agreement_amount', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_agreement_id')->nullable()->constrained('rental_agreement')->onUpdate('cascade')->onDelete('cascade');
            $table->string('date')->nullable();
            $table->float('rental_amount')->nullable();
            $table->float('payment_amount')->nullable();
            $table->float('tds_amount')->nullable();
            $table->integer('year')->nullable();
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->enum('paid_status',['Due', 'Clear', 'Partial']);
            $table->string('remarks')->nullable();
            $table->float('advance_due')->nullable();
            $table->float('previous_due')->nullable();
            $table->tinyInteger('payment_status')->nullable();
            $table->tinyInteger('bank_status')->default('0');    
            $table->softDeletes()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_agreement_amount');
    }
};
