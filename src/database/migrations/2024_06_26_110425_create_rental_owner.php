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
        Schema::create('rental_owner', function (Blueprint $table) {
           $table->id();
           $table->string('owner_name', 191)->nullable();
           $table->string('contact_number', 191)->nullable();

           $table->string('primary_bank_name', 191)->nullable();

           $table->string('primary_account_number', 191)->nullable();
           $table->string('primary_account_name', 191)->nullable();
           $table->string('primary_bank_code', 191)->nullable();
           $table->string('primary_bank_branch', 191)->nullable();
           
           $table->string('secondary_bank_name', 191)->nullable();

           $table->string('secondary_account_number', 191)->nullable();
           $table->string('secondary_account_name', 191)->nullable();
           $table->string('secondary_bank_code', 191)->nullable();
           $table->string('secondary_bank_branch', 191)->nullable();
       
           $table->foreignId('branch_id')->nullable()->constrained('branches');
           $table->foreignId('oc_id')->nullable()->constrained('sub_branches');
        
           $table->foreignId('rental_type_id')->nullable()->constrained('rental_type');

           $table->enum('location_type', ['inside valley','outside valley']);
           $table->enum('payment_type', ['Vianet','LandLord','Lease'])->nullable();
           $table->string('location', 191)->nullable();
           $table->string('status');
           $table->text('termination_clause')->nullable();
           $table->enum('rental_status', ['Submitted','Approved']);
        
           $table->foreignId('added_by')->nullable()->constrained('employees');
           $table->foreignId('approved_by')->nullable()->constrained('employees');

           $table->string('rental_code', 191)->nullable();
           $table->integer('pop_id')->nullable();
           $table->string('grandfather_name', 191)->nullable();
           $table->string('father_name', 191)->nullable();
           $table->bigInteger('citizenship_number')->unsigned()->nullable()->unique();
           $table->bigInteger('customer_id')->nullable();
           $table->timestamps();
           $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_owner');
    }
};
