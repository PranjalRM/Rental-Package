<?php

namespace CodeBright\Rental\Models;

use CodeBright\Rental\Models\RentalAgreement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncrementAmount extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'rental_agreement_amount';

    protected $guarded = ['updated_at','created_at','deleted_at'];

    public function agreement()
    {
        return $this->belongsTo(RentalAgreement::class,'rental_agreement_id');
    }
}
