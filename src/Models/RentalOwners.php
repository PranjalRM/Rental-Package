<?php

namespace Pranjal\Rental\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\configs\Branch;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalOwners extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'rental_owner';
    
    protected $guarded =['updated_at','created_at'];
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    
    public function scopeSearch($query, $value)
    {
        $query->where('owner_name', 'like', "%{$value}%");
    }

    public function agreementStatus()
    {
        return $this->hasMany(RentalAgreement::class,'rental_owner_id');
    }
    public function rentalType()
    {
        return $this->belongsTo(RentalType::class,'rental_type_id');
    }
    public function documents()
    {
        return $this->hasMany(RentalDocument::class,'owner_citizenship_number','citizenship_number');
    }

}
