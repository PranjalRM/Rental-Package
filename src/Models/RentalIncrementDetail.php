<?php

namespace Pranjal\Rental\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalIncrementDetail extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'rental_increment_detail';

    protected $guarded = ['created_at','updated_at','deleted_at'];
    
    public function rentalAgreement()
    {
        return $this->belongsTo(RentalAgreement::class);
    }
}
