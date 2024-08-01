<?php

namespace CodeBright\Rental\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalType extends Model
{
    use HasFactory;

    protected $table = 'rental_type';

    protected $guarded = ['updated_at'];
}
