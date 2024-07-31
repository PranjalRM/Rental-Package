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
        Schema::create('rental_agreement', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('rental_owner_id')->constrained('rental_owner')->onDelete('cascade')->nullable();
            
            $table->string('agreement_date',191)->nullable();
            $table->string('agreement_end_date',191)->nullable();
            $table->float('security_deposit')->nullable();
            $table->float('agreement_period_year')->nullable();
            $table->float('agreement_period_month')->nullable();
            $table->float('gross_rental_amount')->nullable();
            $table->float('net_rental_amount')->nullable();
            $table->float('current_rental_amount')->nullable();
            $table->float('tds')->nullable();
            $table->tinyInteger('tds_payable');
            $table->float('electricity_rate')->nullable();
            $table->float('advance')->nullable();
            $table->enum('payment_period',['monthly', 'quarterly', 'quadrimester']);
            $table->float('total_rent')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->string('terminated_date')->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('locked')->nullable();
            $table->string('district', 191)->nullable();
            $table->string('municipality', 191)->nullable();
            $table->integer('ward_no')->nullable();
            $table->integer('floors_num')->nullable();
            $table->string('agreement_floor', 191)->nullable();
            $table->integer('area_floor')->nullable();
            $table->string('kitta_no', 191)->nullable();
            $table->string('witnesses', 191)->nullable();
            $table->string('place_name', 191)->nullable();
            
            $table->foreignId('amendment_child_id')->constrained('rental_agreement')->nullable();
            
            $table->enum('agreement_status',['Submitted', 'Approved','Rejected']);
            
            $table->foreignId('added_by')->nullable()->constrained('employees');
            $table->foreignId('approved_by')->nullable()->constrained('employees');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agreement');
    }
};
