<?php

namespace Codebright\Rental\Http\Repositories;

use Codebright\Rental\Models\BankCode;

use App\Models\configs\Branch;
use App\Models\configs\SubBranch;

use Codebright\Rental\Models\RentalType;
use Codebright\Rental\Models\RentalOwners;
use Codebright\Rental\Models\RentalDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Models\Employee\Employee;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Codebright\Rental\Models\RentalIncrementDetail;
use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use Codebright\Rental\Models\IncrementAmount;
use Codebright\Rental\Models\RentalAgreement;

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

    public function getSubBranch($branchId)
    {
        return SubBranch::select('id', 'name')->where('branch_id', $branchId)->get();
    }

    public function getRentalType()
    {
        return RentalType::select('id', 'name')->get();
    }

    public function saveOrUpdateRentalOwner($data, $action, $primaryBankName, $secondaryBankName, $chequeImage, $citizenshipImage)
    {
        $authenticatedUserId = Auth::id();
        $employee = Employee::where('user_id', $authenticatedUserId)->first();
        $employeeId = $employee ? $employee->user_id : null;

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

        $this->saveOrUpdateRentalDocument('citizenship', $citizenshipImage, $rentalOwner->id,);
        $this->saveOrUpdateRentalDocument('cheque', $chequeImage, $rentalOwner->id);

        return $message;
    }

    private function saveOrUpdateRentalDocument($type, $image, $ownerId)
    {
        $document = RentalDocument::where('owner_id', $ownerId)
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
                    'owner_id' => $ownerId,
                ]);
            }
        }
    }

    private function saveOrUpdateAgreementDocument($type, $image, $rentalAgreementId, $ownerId)
    {
        $document = RentalDocument::where('agreement_id', $rentalAgreementId)
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
                    'agreement_id' => $rentalAgreementId,
                    'owner_id' => $ownerId,
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
    public function saveAgreement($data, $AgreementDocument)
    {
        $authenticatedUserId = Auth::id();
        $employee = Employee::where('user_id', $authenticatedUserId)->first();
        if ($employee) {
            $employeeId = $employee->id;
        }
        $data['added_by'] = $employeeId;

        $created = RentalAgreement::create($data);
        $this->saveOrUpdateAgreementDocument('agreement', $AgreementDocument, $created->id, $created->owner->id);
        return $created;
    }

    public function updateAgreement($data, $AgreementDocument)
    {
        $agreementId = RentalAgreement::find($data['id']);
        if ($agreementId) {
            $agreementId->update($data);
            $message = "Post Updated Successfully";
        }
        $this->saveOrUpdateAgreementDocument('agreement', $AgreementDocument, $agreementId->id, $agreementId->owner->id);

        return $message;
    }

    public function saveIncrementAmounts($rentalAgreementId, $agreementDate, $agreementEndDate, $grossRentalAmount, $tdsAmount, $advance, $paymentPeriod)
    {
        $incrementAmounts = $this->IncrementAmountCalculation($rentalAgreementId, $agreementDate, $agreementEndDate, $grossRentalAmount, $tdsAmount, $advance, $paymentPeriod);
        IncrementAmount::insert($incrementAmounts);
    }
    public function updateIncrementAmounts($rentalAgreementId, $agreementDate, $agreementEndDate, $grossRentalAmount, $tdsAmount, $advance, $paymentPeriod)
    {
        $incrementAmounts = $this->IncrementAmountCalculation($rentalAgreementId, $agreementDate, $agreementEndDate, $grossRentalAmount, $tdsAmount, $advance, $paymentPeriod);
        IncrementAmount::where('rental_agreement_id', $rentalAgreementId)->delete();
        IncrementAmount::insert($incrementAmounts);
    }

    private function IncrementAmountCalculation(
        $rentalAgreementId,
        $agreementDate,
        $agreementEndDate,
        $grossRentalAmount,
        $tdsRate,
        $advance,
        $paymentPeriod
    ) {
        $startDate = Carbon::parse($agreementDate);
        $endDate = Carbon::parse($agreementEndDate);

        $currentDate = $startDate->copy();
        $grossRentalAmountCurrent = $grossRentalAmount;
        $advanceDue = $advance;
        $incrementAmounts = [];
        $incrementDetails = RentalIncrementDetail::where('rental_agreement_id', $rentalAgreementId)->get();
        $isAdvanceUtilized = false;

        $paymentPeriod = match ($paymentPeriod) {
            'monthly' => 1,
            'quarterly' => 3,
            'quadrimester' => 4,
        };
    
        $paymentCycle = 0;

        while ($currentDate <= $endDate) {
            $currentYear = $currentDate->year;
            $currentMonth = $currentDate->month;

            foreach ($incrementDetails as $detail) {
                $nextIncrementDate = Carbon::parse($detail->next_increment);
                if ($currentYear == $nextIncrementDate->year && $currentMonth == $nextIncrementDate->month) {
                    if ($detail->increment_percent !== null && $detail->increment_amount == null) {
                        $grossRentalAmountCurrent *= (1 + ($detail->increment_percent / 100));
                    } elseif ($detail->increment_amount !== null) {
                        $grossRentalAmountCurrent += $detail->increment_amount;
                    }
                }
            }

            $daysInMonth = $currentDate->daysInMonth;

            $startDay = $currentDate->isSameMonth($startDate) ? $startDate->day : 1;
            $endDay = $currentDate->isSameMonth($endDate) ? $endDate->day : $daysInMonth;
            $daysInPeriod = $endDay - $startDay + 1;

            $netRentalAmount = ($grossRentalAmountCurrent * $daysInPeriod) / $daysInMonth;
            $tdsAmount = ($netRentalAmount * $tdsRate) / 100;
            $paymentAmount = $netRentalAmount - $tdsAmount;

            if ($paymentCycle % $paymentPeriod == 0) {
                $totalPaymentAmount = $paymentAmount * $paymentPeriod;
                $totalTdsAmount = $tdsAmount * $paymentPeriod;
                $advancePayment = $totalPaymentAmount;
    
                if (!$isAdvanceUtilized) {
                    if ($advanceDue > 0) {
                        if ($advanceDue >= $advancePayment) {
                            $paidStatus = "Clear";
                            $advanceDue -= $advancePayment;
                        } else {
                            $paidStatus = "Partial";
                            $advanceDue = 0;
                            $isAdvanceUtilized = true;
                        }
                    } else {
                        $paidStatus = "Due";
                    }
                } else {
                    $paidStatus = "Due";
                }
    
                $amount = [
                    'rental_agreement_id' => $rentalAgreementId,
                    'date' => $currentDate->format('Y-m-d'),
                    'rental_amount' => $paymentAmount,
                    'year' => $currentYear,
                    'month' => $currentMonth,
                    'payment_amount' => $totalPaymentAmount,
                    'TDS_amount' => $totalTdsAmount,
                    'advance_due' => $advanceDue,
                    'paid_status' => $paidStatus,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                $incrementAmounts[] = $amount;
    
            } else {
                $incrementAmounts[] = [
                    'rental_agreement_id' => $rentalAgreementId,
                    'date' => $currentDate->format('Y-m-d'),
                    'rental_amount' => $netRentalAmount,
                    'year' => $currentYear,
                    'month' => $currentMonth,
                    'payment_amount' => 0,
                    'TDS_amount' => 0,
                    'advance_due' => $advanceDue,
                    'paid_status' => "No Payment Due",
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $currentDate->addMonth()->startOfMonth();
        }

        return $incrementAmounts;
    }
}
