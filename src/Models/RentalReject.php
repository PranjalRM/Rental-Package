<?php

namespace CodeBright\Rental\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalReject extends Model
{
    use HasFactory,SoftDeletes;

    protected $table='hrm_rental_reject' ;  
}
