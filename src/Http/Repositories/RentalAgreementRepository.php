<?php

namespace CodeBright\Rental\Http\Repositories;

use CodeBright\Rental\Models\BankCode;
use App\Models\configs\Branch;
use App\Models\configs\SubBranch;
use CodeBright\Rental\Models\RentalType;
use CodeBright\Rental\Models\RentalOwners;
use CodeBright\Rental\Models\RentalDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use CodeBright\Rental\Models\RentalIncrementDetail;
use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use CodeBright\Rental\Models\IncrementAmount;
use CodeBright\Rental\Models\RentalAgreement;

class RentalAgreementRepository extends Repository
{
    public function getBankCodes()
    {
        return BankCode::select('id', 'name', 'code')->get();
    }

    public function getBranch()
    {
        return Branch::select('id', 'name')->get();
    }

    public function getSubBranch()
    {
        return SubBranch::select('id', 'name')->get();
    }

    public function getRentalType()
    {
        return RentalType::select('id', 'name')->get();
    }

    public function saveOrUpdateRentalOwner($data, $action, $primaryBankName, $secondaryBankName, $chequeImage, $citizenshipImage)
    {
        $authenticatedUserId = Auth::id();
        $employee = Employee::where('user_id', $authenticatedUserId)->first();
        $employeeId = $employee ? $employee->id : null;
        $data['added_by'] = $employeeId;

        if ($action === 'create') {
            $rentalOwner = RentalOwners::create($data);
            $message = "Post Created Successfully";
        } elseif ($action === 'update') {
            $rentalOwner = RentalOwners::find($data['id']);

            if ($rentalOwner) {
                $rentalOwner->update($data);            
                $message = "Post Updated Successfully";
            }
        }

        $this->saveOrUpdateDocuments('citizenship', $citizenshipImage, $rentalOwner->id, );
        $this->saveOrUpdateDocuments('cheque', $chequeImage, $rentalOwner->id);

        return $message;
    }

    public function saveAgreement($data,$AgreementDocument)
    {
        $authenticatedUserId = Auth::id();
            $employee = Employee::where('user_id', $authenticatedUserId)->first();
            $employeeId = $employee ? $employee->id : null;
            $data['added_by'] = $employeeId;
            
        $data['added_by'] = $employeeId;

        $created=RentalAgreement::create($data);
        $this->saveOrUpdateDocuments('agreement',$AgreementDocument,$created->owner->id,$created->id);            
        return $created;
    }

    public function updateAgreement($data,$AgreementDocument)
    {
        $agreementId = RentalAgreement::find($data['id']);
        if($agreementId) {
            $agreementId->update($data);
            $message = "Post Updated Successfully";
        }

        $this->saveOrUpdateDocuments('agreement',$AgreementDocument,$agreementId->owner->id,$agreementId->id);            
        return $message;
    }

    public function saveIncrementAmounts($rentalAgreementId,$agreementDate,$agreementEndDate,$grossRentalAmount,$tdsPayable,$advance)
    {
        $incrementAmounts = $this->IncrementAmountCalculation($rentalAgreementId,$agreementDate,$agreementEndDate,$grossRentalAmount,$tdsPayable,$advance);
        IncrementAmount::insert($incrementAmounts);
    }
    public function updateIncrementAmounts($rentalAgreementId,$agreementDate,$agreementEndDate,$grossRentalAmount,$tdsPayable,$advance)
    {
        $incrementAmounts = $this->IncrementAmountCalculation($rentalAgreementId,$agreementDate,$agreementEndDate,$grossRentalAmount,$tdsPayable,$advance);
        IncrementAmount::where('rental_agreement_id', $rentalAgreementId)->delete();
        IncrementAmount::insert($incrementAmounts);
    }

    public function saveOrUpdateDocuments($type,$image, $ownerId ,$rentalAgreementId = null)
    {
        $query = RentalDocument::where('type', $type);

        if ($rentalAgreementId) {
            $query = $query->where('agreement_id', $rentalAgreementId);
        } else {
            $query = $query->where('owner_id',$ownerId);
        }

        $document = $query->first();
        if ($image instanceof UploadedFile) {
            $filePath = $document ? $document->image_path : null;
            if ($document && $filePath) {
                Storage::delete('public/'. $filePath);
            }

            $imagePath = $this->saveDocument($image, $type);

            if ($document) {
                $document->image_path = $imagePath;
                $document->save();
            } else {
                RentalDocument::create([
                    'type' => $type,
                    'image_path' => $imagePath,
                    'owner_id' => $ownerId,
                    'agreement_id' => $rentalAgreementId,
                ]);
            }
        }
    }
    private function saveDocument($file, $type)
    {
        if ($file instanceof UploadedFile) {
            $filePath = $file->store('public/documents');
            $filePath = str_replace('public/', '', $filePath);
            return $filePath;
        } elseif (is_string($file)) {
            return $file;
        }
        return null;
    } 

    private function IncrementAmountCalculation($rentalAgreementId,$agreementDate,$agreementEndDate,$grossRentalAmount,$tdsPayable,$advance)
    {
        $startDate = Carbon::parse($agreementDate);
        $endDate = Carbon::parse($agreementEndDate);
        $tdsAmount= 0;
        $paymentAmount = 0;
        $incrementDetails = RentalIncrementDetail::where('rental_agreement_id', $rentalAgreementId)->get();
        $currentDate = $startDate->copy();
        $incrementAmounts = [];
        $gross_rental_amount = $grossRentalAmount;

        while ($currentDate <= $endDate || $currentDate->month === $endDate->month) {
            $currentYear = $currentDate->year;
            $currentMonth = $currentDate->month;
            $startOfMonth = $startDate->copy()->startOfMonth();
            $endOfMonth = $endDate->copy()->endOfMonth();
            $daysInMonth = LaravelNepaliDate::daysInMonth($currentMonth, $currentYear);
            foreach ($incrementDetails as $detail) {
                $nextIncrementDate = Carbon::parse($detail->next_increment);
                if ($currentYear ==($nextIncrementDate->year) && $currentMonth == ($nextIncrementDate->month)) {
                    if ($detail->increment_percent !== null && $detail->increment_amount == null) {
                        $gross_rental_amount *= (1 + ($detail->increment_percent / 100));
                    }else {
                        $gross_rental_amount += $detail->increment_amount;
                    }
                }
            }
            if ($currentDate->month === $startOfMonth->month && $currentDate->year === $startOfMonth->year) {
                $startDay = $startDate->day;
            } else {
                $startDay = 1;
            }
        
            if ($currentDate->month === $endOfMonth->month && $currentDate->year === $endOfMonth->year) {
                $endDay = $endDate->day;
            } else {
                $endDay = $daysInMonth;
            }
        
            $daysInPeriod = $endDay - $startDay + 1;
            $netRentalAmount = $gross_rental_amount * $daysInPeriod / $daysInMonth;
            $tdsAmount = $netRentalAmount / $tdsPayable;
            $paymentAmount = $netRentalAmount - $tdsAmount;
            $amount = [
                'rental_agreement_id' => $rentalAgreementId,
                'date' => $currentDate->format('Y-m-d'),
                'rental_amount' => $netRentalAmount,
                'year' => $currentDate->year,
                'month' => $currentDate->month,
                'payment_amount' => $paymentAmount,
                'TDS_amount' => $tdsAmount,
                'advance_due' => $advance ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $incrementAmounts[] = $amount;
            if ($currentDate->copy()->addMonth()->startOfMonth() === $endDate->copy()->month()) {
                break;
            }
            $currentDate->addMonth();
        }
        return $incrementAmounts;
    }
}
