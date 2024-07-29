<?php

namespace Pranjal\Rental\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalDocument extends Model
{
    use HasFactory;

    protected $table = 'rental_owner_documents';
    
    protected $guarded=['created_at','updated_at','deleted_at'];

    public function images()
    {
        return $this->belongsTo(RentalOwners::class, 'owner_citizenship_number', 'citizenship_number');
    }

    public function documents()
    {
        return $this->belongsTo(RentalAgreement::class, 'rental_agreement_id');   
     }
}
