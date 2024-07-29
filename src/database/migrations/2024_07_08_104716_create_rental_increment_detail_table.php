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
        Schema::create('rental_increment_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_agreement_id')->nullable()->constrained('rental_agreement')->onDelete('cascade')
            ->onUpdate('cascade');
            $table->float('increment_percent')->nullable();
            $table->float('increment_amount')->nullable();
            $table->float('increment_after')->nullable();
            $table->string('next_increment')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_increment_detail');
    }
};
