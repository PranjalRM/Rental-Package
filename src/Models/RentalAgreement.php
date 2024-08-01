<?php

namespace CodeBright\Rental\Models;

use CodeBright\Rental\Http\Controllers\Rental\RentalOwner;
use CodeBright\Rental\Models\IncrementAmount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalAgreement extends Model
{
    use HasFactory;

    protected $table = 'rental_agreement';
    protected $guarded = ['created_at','updated_at'];

    public function amendment()
    {
        return $this->belongsTo(RentalAgreement::class, 'amendment_child_id');
    }

    public function owner()
    {
        return $this->belongsTo(RentalOwners::class,'rental_owner_id');
    }

    public function file()
    {
        return $this->hasMany(RentalDocument::class,'agreement_id');
    }

    public function scopeSearch($query, $value)
    {
        $query->where('id', 'like', "%{$value}%");
    }

    public function details()
    {
        return $this->hasMany(IncrementAmount::class,'rental_agreement_id');
    }
    public function rentalIncrementDetail()
    {
        return $this->hasMany(RentalIncrementDetail::class, 'rental_agreement_id');
    }
}
