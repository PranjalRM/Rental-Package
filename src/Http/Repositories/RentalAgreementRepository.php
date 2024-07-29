<?php

namespace Pranjal\Rental\Http\Repositories;

use Pranjal\Rental\Models\BankCode;

use App\Models\configs\Branch;
use App\Models\configs\SubBranch;

use Pranjal\Rental\Models\RentalType;
use Pranjal\Rental\Models\RentalOwners;
use Pranjal\Rental\Models\RentalDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\Models\Employee\Employee;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Pranjal\Rental\Models\RentalIncrementDetail;
use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use Pranjal\Rental\Models\IncrementAmount;
use Pranjal\Rental\Models\RentalAgreement;

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

        $this->saveOrUpdateRentalDocument('citizenship', $citizenshipImage, $rentalOwner->citizenship_number, );
        $this->saveOrUpdateRentalDocument('cheque', $chequeImage, $rentalOwner->citizenship_number);

        return $message;
    }

    private function saveOrUpdateRentalDocument($type, $image, $citizenshipNumber)
    {
        $document = RentalDocument::where('owner_citizenship_number', $citizenshipNumber)
            ->where('type', $type)
            ->first();

        if ($image instanceof UploadedFile) {
            $filePath = $document ? $document->image_path : null;
            if ($document) {
                Storage::delete('public/' . $filePath);
            }

            $imagePath = $this->saveDocument($image, $type);
            if ($document) {
                $document->image_path = $imagePath;
                $document->save();
            } else {
                RentalDocument::create([
                    'type' => $type,
                    'image_path' => $imagePath,
                    'owner_citizenship_number' => $citizenshipNumber
                ]);
            }
        }
    }

    private function saveOrUpdateAgreementDocument($type,$image, $rentalAgreementId,$citizenshipNumber)
    {
        $document = RentalDocument::where('rental_agreement_id', $rentalAgreementId)
            ->where('type', $type)
            ->first();
            if ($image instanceof UploadedFile) {
                $filePath = $document ? $document->image_path : null;
                if ($document) {
                    Storage::delete('public/' . $filePath);
                }
    
                $imagePath = $this->saveDocument($image, $type);
                if ($document) {
                    $document->image_path = $imagePath;
                    $document->save();
                } else {
                    RentalDocument::create([
                        'type' => $type,
                        'image_path' => $imagePath,
                        'rental_agreement_id' =>$rentalAgreementId,
                        'owner_citizenship_number' => $citizenshipNumber,
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
    public function saveAgreement($data,$AgreementDocument)
    {
        $authenticatedUserId = Auth::id();
            $employee = Employee::where('user_id', $authenticatedUserId)->first();
            if ($employee) {
                $employeeId = $employee->id;
            }
        $data['added_by'] = $employeeId;

        $created=RentalAgreement::create($data);
        $this->saveOrUpdateAgreementDocument('agreement',$AgreementDocument,$created->id,$created->owner->citizenship_number);            
        return $created;
    }

    public function updateAgreement($data,$AgreementDocument)
    {
        $agreementId = RentalAgreement::find($data['id']);
        if($agreementId) {
            $agreementId->update($data);
            $message = "Post Updated Successfully";
        }
        $this->saveOrUpdateAgreementDocument('agreement',$AgreementDocument,$agreementId->id,$agreementId->owner->citizenship_number);            

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

        while ($currentDate <= $endDate) {
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
            if ($currentDate->month === $startOfMonth->month && $currentDate->year === $startDate->year) {
                $days = $daysInMonth - $startDate->day + 1;
                $netRentalAmount = $gross_rental_amount * $days / $daysInMonth;
                $tdsAmount = $netRentalAmount/$tdsPayable;
                $paymentAmount = $netRentalAmount - $tdsAmount;

            } elseif ($currentDate->month === $endOfMonth->month && $currentDate->year === $endDate->year) {
                $days = $endDate->day;
                $netRentalAmount = $gross_rental_amount * $days / $daysInMonth;
                $tdsAmount = $netRentalAmount/$tdsPayable;
                $paymentAmount = $netRentalAmount - $tdsAmount;

            } else {
                $tdsAmount = $gross_rental_amount/$tdsPayable;
                $netRentalAmount = $gross_rental_amount-$tdsAmount;
                $paymentAmount = $netRentalAmount - $tdsAmount;
            }
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
            $currentDate->addMonth();
        }
        return $incrementAmounts;
    }
}
