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
        Schema::create('rental_owner_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('owner_id')
                ->constrained('rental_owner')
                ->nullable()
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreignId('agreement_id')
                ->constrained('rental_agreement')
                ->nullable()
                ->onDelete('cascade')
                ->onUpdate('cascade');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_owner_documents');
    }
};
