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
        Schema::create('hrm_rental_reject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('rental_owner')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('agreement_id')
                ->constrained('rental_agreement')
                ->nullable()
                ->onDelete('cascade')
                ->onUpdate('cascade'); 
            $table->text('reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hrm_rental_reject');
    }
};
