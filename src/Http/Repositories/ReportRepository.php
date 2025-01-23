<?php

namespace Codebright\Rental\Http\Repositories;

use App\Traits\WithDataTable;
use Codebright\Rental\Models\ElectricityBills;
use Codebright\Rental\Models\RentalAgreement;
use Illuminate\Database\Eloquent\Collection;

class ReportRepository extends Repository
{
    use WithDataTable;
    public function getRentalReport(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = RentalAgreement::query()
            ->join('rental_owner', 'rental_agreement.rental_owner_id', '=', 'rental_owner.id')
            ->join('branches', 'rental_owner.branch_id', '=', 'branches.id')
            ->join('rental_type', 'rental_owner.rental_type_id', '=', 'rental_type.id')
            ->join('rental_increment_detail', 'rental_agreement.id', '=', 'rental_increment_detail.rental_agreement_id')
            ->join('rental_agreement_amount', 'rental_increment_detail.rental_agreement_id', '=', 'rental_agreement_amount.rental_agreement_id')
            ->select(
                'rental_owner.owner_name as owner_name',
                'rental_owner.location',
                'branches.name as branch',
                'rental_agreement.payment_period',
                'rental_type.name as rental_type',
                'rental_agreement_amount.Tds_amount as tds',
                'rental_owner.primary_bank_name',
                'rental_owner.primary_account_name',
                'rental_owner.primary_account_number',
                'rental_owner.payment_type',
                'rental_owner.rental_code as sub_ledger_code',
                'rental_owner.location_type',
                'rental_agreement.electricity_rate',
                'branches.name as branch',
                'rental_agreement_amount.rental_amount',
                'rental_agreement_amount.advance_due',
                'rental_agreement_amount.previous_due',
                'rental_agreement_amount.payment_amount',
                'rental_agreement_amount.paid_status'
            )
            ->where('agreement_status', 'Approved')
            ->whereHas('owner', function ($query) {
                $query->where('rental_status', 'Approved');
            })
            ->whereNull('rental_agreement_amount.deleted_at')
            ->whereNull('rental_increment_detail.deleted_at');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['fiscalYear'])) {
            $query->where('rental_agreement_amount.year', $filters['fiscalYear']);
        }
        if (!empty($filters['owner']) && $filters['owner'] !== 'All') {
            $query->where('rental_owner.owner_name', $filters['owner']);
        }
        if (!empty($filters['month'])) {
            $query->where('rental_agreement_amount.month', $filters['month']);
        }
        if (!empty($filters['rentalTypes']) && $filters['rentalTypes'] !== 'All') {
            $query->where('rental_type.name', $filters['rentalTypes']);
        }
        if (!empty($filters['branch']) && $filters['branch'] !== 'All') {
            $query->where('rental_owner.branch_id', $filters['branch']);
        }

        $data = $query->paginate($this->perPage);

        $data->getCollection()->transform(function ($item) use ($filters) {
            return $this->applyCalculations($item, $filters);
        });
        return $data;
    }

    private function applyCalculations($item, $filters)
    {
        $year = $filters['fiscalYear'];
        $month = $filters['month'];


        $item->gross_amount = $item->rental_amount + $item->tds;
        if (is_null($item->terminated_date) || (!is_null($item->terminated_date) && $item->date < $item->terminated_date)) {
            $electricity_bill = ElectricityBills::where([
                ['rental_agreement_id', $item->rental_agreement_id],
                ['year', $year],
                ['month', $month]
            ])->first();

            if ($electricity_bill) {
                $item->electricity_amount = $electricity_bill->amount;
                $item->extra_charges = $electricity_bill->extra_charges;
                $item->previous_meter_reading = $electricity_bill->previous_meter_reading;
                $item->current_meter_reading = $electricity_bill->current_meter_reading;
                $item->conusmption = $electricity_bill->current_meter_reading - $electricity_bill->previous_meter_reading;
            } else {
                $item->electricity_amount = 0;
                $item->extra_charges = 0;
                $item->previous_meter_reading = 0;
                $item->current_meter_reading = 0;
                $item->conusmption = 0;
            }
        }

        if ($item->payment_type == 'Owner') {
            $item->total = $item->electricity_amount + $item->extra_charges + $item->payment_amount + $item->tds;
        } else {
            $item->total = $item->electricity_amount + $item->extra_charges + $item->payment_amount;
        }

        if ($item->advance_due < 0 && $item->payment_amount == 0) {
            $item->advance = 0;
        } elseif ($item->advance_due != 0) {
            $item->advance = $item->payment_amount + $item->advance_due;
        } else {
            $item->advance = $item->advance_due;
        }

        if ($item->advance_due <= 0 || $item->payment_amount == 0) {
            $item->adjusted_advance_due = 0;
        } else {
            $item->adjusted_advance_due = $item->advance_due;
        }

        if ($item->adjusted_advance_due <= 0 || $item->payment_amount == 0) {
            $item->paid = 0;
        }
        if ($item->adjusted_advance_due < 0) {
            $item->paid = $item->electricity_amount + $item->extra_charges - $item->adjusted_advance_due;
        } elseif ($item->adjusted_advance_due > 0) {
            $item->paid = $item->electricity_amount + $item->extra_charges;
        } else {
            if ($item->paid_status == 'Clear') {
                $item->paid = $item->electricity_amount + $item->extra_charges;
            } else {
                $item->paid = $item->electricity_amount + $item->extra_charges + $item->payment_amount;
            }
        }
        return $item;
    }
}
