<?php

namespace Codebright\Rental\Models;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use Carbon\Carbon;
use Codebright\Rental\Http\Controllers\Rental\RentalOwner;
use Codebright\Rental\Models\IncrementAmount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalAgreement extends Model
{
    use HasFactory;

    protected $table = 'rental_agreement';
    protected $guarded = ['created_at', 'updated_at'];

    public function amendment()
    {
        return $this->belongsTo(RentalAgreement::class, 'amendment_child_id');
    }

    public function owner()
    {
        return $this->belongsTo(RentalOwners::class, 'rental_owner_id');
    }

    public function file()
    {
        return $this->hasMany(RentalDocument::class, 'agreement_id');
    }

    public function scopeSearch($query, $value)
    {
        return $query->whereHas('owner', function ($query) use ($value) {
            $query->where('owner_name', 'like', "%{$value}%");
        });
    }

    public function details()
    {
        return $this->hasMany(IncrementAmount::class, 'rental_agreement_id');
    }
    public function rentalIncrementDetail()
    {
        return $this->hasMany(RentalIncrementDetail::class, 'rental_agreement_id');
    }

    public function getRemainingDaysAttribute()
    {
        if ($this->agreement_end_date && !$this->terminated_date) {
            $agreementEndDateEng = LaravelNepaliDate::from($this->agreement_end_date)->toEnglishDate('Y-m-d');
        } else if ($this->agreement_end_date && $this->terminated_date) {
            $agreementEndDateEng = LaravelNepaliDate::from($this->terminated_date)->toEnglishDate('Y-m-d');
        }

        if ($agreementEndDateEng && Carbon::now()->lt($agreementEndDateEng)) {
            $now = Carbon::now();

            $years = $now->diffInYears($agreementEndDateEng);
            $now = $now->addYears($years);

            $months = $now->diffInMonths($agreementEndDateEng);
            $now = $now->addMonths($months);

            $days = $now->diffInDays($agreementEndDateEng);
            if ($days <= 0) {
                $days = 0;
            }

            $result = [];
            if ($years > 0) {
                $result[] = "$years Year(s)";
            }
            if ($months > 0 || $years > 0) {
                $result[] = "$months Month(s)";
            }
            $result[] = "$days Day(s)";

            return implode(' ', $result);
        }
        return '0 Day(s)';
    }

    public function getRemainingDaysColorAttribute()
    {
        if ($this->agreement_end_date && !$this->terminated_date) {
            $agreementEndDateEng = LaravelNepaliDate::from($this->agreement_end_date)->toEnglishDate('Y-m-d');
        } else if ($this->agreement_end_date && $this->terminated_date) {
            $agreementEndDateEng = LaravelNepaliDate::from($this->terminated_date)->toEnglishDate('Y-m-d');
        }
        if (!$agreementEndDateEng || Carbon::now()->gte($agreementEndDateEng)) {
            return 'red';
        }

        $now = Carbon::now();
        $monthsRemaining = $now->diffInMonths($agreementEndDateEng);

        if ($monthsRemaining < 3) {
            return 'yellow';
        }

        return '';
    }
}
