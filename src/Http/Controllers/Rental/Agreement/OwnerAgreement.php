<?php

namespace CodeBright\Rental\Http\Controllers\Rental\Agreement;

use Livewire\Component;
use CodeBright\Rental\Models\RentalOwners;
use CodeBright\Rental\Models\RentalAgreement;
use App\Traits\WithDataTable;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Http\UploadedFile;
use CodeBright\Rental\Models\RentalDocument;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use CodeBright\Rental\Exports\AgreementReportExport;
use CodeBright\Rental\Models\IncrementAmount;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee\Employee;
use CodeBright\Rental\Models\RentalReject;
use CodeBright\Rental\Http\Repositories\RentalAgreementRepository;
use Livewire\Attributes\Title;

#[Title('Rental Agreement')]
class OwnerAgreement extends Component
{
    use WithDataTable ,WithFileUploads;
  
    public $ownerId;
    public $owner;
    public $agreements;
    public $documentType="branchRenewal";
    public $agreementData;
    public $reason;
    public $terminated_date;
    
    #[Validate('mimes:pdf|max:7168')]
    public $file;

    private RentalAgreementRepository $repository;
    public function __construct()
    {
        $this->repository = new RentalAgreementRepository;
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
        $agreement=RentalDocument::with('documents')->where('agreement_id', $agreementId->id)->get();
        foreach($agreement as $document){
            $filePath = $document->image_path;
            Storage::delete('public/'.$filePath);
            $document->delete();
        }
            $agreementId -> delete();
            $message = 'Rental owner deleted successfully';
            unset($this->loadAgreements);
            $this->notify($message)->send();
    }

    public function uploadDocument($agreementId)
    {   
        $this->validate();
        $this->repository->saveOrUpdateDocuments($this->documentType, $this->file, $this->ownerId,$agreementId);

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
        $agreement = RentalAgreement::findOrFail($agreementId);
        
        $reject = new RentalReject();
        $reject->reason = $this->reason;
        $reject->owner_id = $agreement->owner->id; 
        $reject->agreement_id = $agreement->id;
        $reject->save();

        $agreement->agreement_status = 'Rejected';
        $agreement->save();

        $this->dispatch('hide-model');
        $this->notify('Reason added successfully')->send();
        unset($this->list); 
        $this->reason = '';
    }

    public function addTerminateDate($agreementId)
    {
        $this->validate(['terminated_date' => 'required|date']);
        $agreement = RentalAgreement::findOrFail($agreementId);
        $agreement->terminated_date = $this->terminated_date;
        $this->repository->updateIncrementAmounts($agreement->id, 
                                                  $agreement->agreement_date,
                                                  $agreement->terminated_date, 
                                                  $agreement->gross_rental_amount, 
                                                  $agreement->tds_payable,
                                                  $agreement->advance);
        $agreement->save();

        $this->dispatch('hide-model');
        $this->notify('Termination date updated successfully')->send();
        unset($this->loadAgreements);
    }


    public function render()
    {
        return view('rental::rental.agreement.owner-agreement');
    }
    
}
