<?php

namespace Codebright\Rental\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElectricityBills extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rental_electricity_bill';

    protected $guarded = ['updated_at','created_at','deleted_at'];

    public function agreementOwner()
    {
        return $this->belongsTo(RentalAgreement::class,'rental_agreement_id');
    }

}