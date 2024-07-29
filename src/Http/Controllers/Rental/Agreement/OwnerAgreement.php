<?php

namespace Pranjal\Rental\Http\Controllers\Rental\Agreement;

use Livewire\Component;
use Pranjal\Rental\Models\RentalOwners;
use Pranjal\Rental\Models\RentalAgreement;
use App\Traits\WithDataTable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Http\UploadedFile;
use Pranjal\Rental\Models\RentalDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Pranjal\Rental\Exports\AgreementReportExport;
use Pranjal\Rental\Models\IncrementAmount;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee\Employee;
use Pranjal\Rental\Models\RentalReject;

class OwnerAgreement extends Component
{
    use WithDataTable ,WithFileUploads;
  
    public $ownerId;
    public $owner;
    public $agreements;
    public $documentType="branchRenewal";
    public $agreementData;
    public $reason;
    
    #[Validate('mimes:pdf|max:7168')]
    public $file;


    public function __construct()
    {
        $this->tableListVariable = "loadAgreements";
    }

    public function mount($ownerId)
    {
        $this->ownerId = $ownerId;
        $this->owner = RentalOwners::find($ownerId);
    }
    
    #[Computed(persist:true)]
    public function loadAgreements()
    {
        $loadAgreements =RentalAgreement::where('rental_owner_id', $this->ownerId,)->with('file')->search($this->search)->orderByDesc('created_at');
        $loadAgreements = $this->applySorting($loadAgreements)->paginate($this->perPage);
        return $loadAgreements;
    }

    public function approve($agreementId)
    {
        $agreement = RentalAgreement::findOrFail($agreementId);
        $agreement->agreement_status = 'Approved';
        $authenticatedUserId = Auth::id();
        $employee = Employee::where('user_id', $authenticatedUserId)->first();
        if ($employee) {
            $agreement->approved_by = $employee->id;
            $agreement->save();
        }
        $agreement->save();
        
        $message = "Post Updated Successfully";
        $this->notify($message)->send();
        unset($this->loadAgreements);
    }

    public function delete(RentalAgreement $agreementId)
    {
            $agreementId -> delete();
            $message = 'Rental owner deleted successfully';
            unset($this->loadAgreements);
            $this->notify($message)->send();
    }

    public function uploadDocument($agreementId)
    {   
        $this->validate();
        $agreementDocumentPath = RentalDocument::where('rental_agreement_id',$agreementId)
                ->where('type',$this->documentType)
                ->first();
        if($agreementDocumentPath)
        {
            if($this->file instanceof UploadedFile){
                $filePath = $agreementDocumentPath->image_path;
                Storage::delete('public/'.$filePath);
                $agreementDocumentPath->image_path = $this->saveDocument($this->file, $this->documentType);
                    $this->file = $agreementDocumentPath->image_path;
                    $agreementDocumentPath->save();
            }
        } else{
            $agreement = RentalAgreement::findOrFail($agreementId);
            $agreementImagePath = $this->saveDocument($this->file, $this->documentType);
            RentalDocument::create([
                'type' => $this->documentType,
                'image_path' => $agreementImagePath ? : null,
                'rental_agreement_id' =>$agreementId,
                'owner_citizenship_number' => $this->owner->citizenship_number,
            ]);
        }
        $this->dispatch('hide-model');
        $message = "Document Uploaded successfully.";
        unset($this->loadAgreements);
        $this->notify($message)->send(); 
    }

    public function view(RentalAgreement $agreementId)
    {
        return redirect()->route('viewAgreement',['agreementId' => $agreementId]);
    }
    
    public function edit(RentalAgreement $agreementId)
    {
        return redirect()->route('editAgreement',['agreementEditId' => $agreementId]);
    }
    public function export($agreementId)
    {
        $incrementAmounts = IncrementAmount::where('rental_agreement_id', $agreementId)->get();
        return Excel::download(new AgreementReportExport($incrementAmounts), 'agreement_details.xlsx');  
    }
    public function copy(RentalAgreement $agreementId)
    {
        return redirect()->route('copyAgreement',['copyOwnerId' => $agreementId]);
    }

    public function addReason($agreementId)
    {
        $this->validate(['reason' => 'required|string|min:5']);
        $owner = RentalOwners::findOrFail($agreementId);
        
        $reject = new RentalReject();
        $reject->reason = $this->reason;
        $reject->owner_id = $owner->id; 
        $reject->save();

        $owner->status = 'Rejected';
        $owner->save();

        $this->dispatch('hide-model');
        $this->notify('Reason added successfully')->send();
        unset($this->list); 
        $this->reason = '';
    }

    public function render()
    {
        return view('livewire.config.rental.agreement.owner-agreement');
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
    
}