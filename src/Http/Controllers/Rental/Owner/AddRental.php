<?php

namespace Codebright\Rental\Http\Controllers\Rental\Owner;

use Codebright\Rental\Http\Repositories\RentalAgreementRepository;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use App\Traits\WithNotify;
use Codebright\Rental\Models\RentalOwners;
use Codebright\Rental\Models\BankCode;
use Codebright\Rental\Models\RentalDocument;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;

#[Title('Add Rental Owner')]
class AddRental extends Component
{
    use WithFileUploads, WithNotify;

    public $currentAction = 'add';
    public $confirmed = false;
    public $ownerId;

    #[Validate('required|string')]
    public $owner_name = '';

    #[Validate('nullable|integer|digits:10')]
    public $contact_number;

    #[Validate('required|string')]
    public $grandfather_name = '';

    #[Validate('required|string')]
    public $father_name = '';

    #[Validate('required|integer')]
    public $citizenship_number = '';

    #[validate('required')]
    public $primary_bank_name = '';

    #[Validate('required|string')]
    public $primary_account_name = '';

    #[Validate('required|string')]
    public $primary_account_number = '';

    #[Validate('required|string')]
    public $primary_bank_branch = '';

    public $secondary_bank_name = '';

    #[Validate('string|nullable')]
    public $secondary_account_name;

    #[Validate('string|nullable')]
    public $secondary_account_number;

    #[Validate('string|nullable')]
    public $secondary_bank_branch;

    #[Validate('max:50|in:inside valley,outside valley')]
    public string $location_type = 'inside valley';

    #[Validate('max:50|in:Vianet,Landlord,Lease')]
    public string $payment_type = 'Vianet';

    #[Validate('required')]
    public $branch_id = '';


    public $rental_code;
    public $oc_id = '';
    public $rental_type_id = '';
    public $status = 0;

    public $customer_id;

    public $citizenshipImage = '';
    public $chequeImage = '';

    public $existingCitizenshipImage = '';
    public $existingChequeImage = '';

    #[Validate('string')]
    public $termination_clause = '';

    #[Validate('required|string')]
    public $location = '';

    public $bankArray = [];
    public $branchName = [];
    public $subBranchName = [];
    public $rentalTypeArray = [];
    public $data = [];
    protected $model;
    public $rentalOwner;

    private RentalAgreementRepository $repository;

    public function rules()
    {
        return [
            'citizenshipImage' => [...($this->currentAction === 'edit' ? ['nullable'] : ['required']), 'mimes:pdf', 'max:7168'],
            'chequeImage' => [...($this->currentAction === 'edit' ? ['nullable'] : ['required']), 'mimes:pdf', 'max:7168'],
            'citizenship_number' => [...($this->currentAction === 'edit' ? ['required'] : [Rule::unique('rental_owner', 'citizenship_number')]), 'required'],
            'customer_id' => [...($this->currentAction === 'edit' ? ['nullable'] : ['nullable']), 'nullable', 'integer']
        ];
    }

    public function validationAttributes()
    {
        return [
            'owner_name' => 'Owner Name',
            'contact_number' => 'Contact Number',
            'grandfather_name' => 'Grandfather/Father\'s Name',
            'citizenship_number' => 'Citizenship Number',
            'primary_bank_name' => 'Primary Bank Name',
            'primary_account_name' => 'Primary Bank Account Name',
            'primary_account_number' => 'Primary Bank Account Number',
            'primary_bank_branch' => 'Primary Bank Branch',
            'father_name' => 'Father\'s/Husband Name',
            'branch_id' => 'Branch',
            'location' => 'Location',
            'customer_id' => 'Vianet Customer ID',
            'chequeImage' => 'Cheque Image',
            'citizenshipImage' => 'Citizenship Image',
        ];
    }
    public function __construct()
    {
        $this->model = new RentalOwners;
        $this->repository = new RentalAgreementRepository;
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->currentAction = 'edit';
            $this->ownerId = $id;
            $this->fillFormModel($id);
            $this->loadOwnerDetails();
        }
        $this->bankArray = $this->repository->getBankCodes();
        $this->branchName = $this->repository->getBranch();
        // $this->subBranchName = $this->repository->getSubBranch();
        $this->rentalTypeArray = $this->repository->getRentalType();
    }

    public function save()
    {
        $this->validate();
        $this->performSaveOrUpdate('create');
    }

    public function edit()
    {
        $this->validate();
        $this->performSaveOrUpdate('update');
    }

    public function clearForm()
    {
        $this->confirmed = false;
        $this->reset();
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('rental::rental.owner.add-rental');
    }

    public function updated($propertyName)
    {
        if ($propertyName == 'confirmed' && !$this->confirmed) {
            $this->customer_id = null;
        }
        if ($propertyName = 'branch_id') {
            $this->subBranchName = $this->repository->getSubBranch($this->branch_id);
        }
    }
    #[Computed()]
    public function fillFormModel($id)
    {
        $row = $this->model->findOrFail($id);
        $attributes = $row->getAttributes();
        $guarded = $row->getGuarded();
        $fillableAttributes = array_diff_key($attributes, array_flip($guarded));

        foreach ($fillableAttributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    private function dataField()
    {
        return [
            'id' => 'ownerId',
            'owner_name' => 'owner_name',
            'contact_number' => 'contact_number',
            'grandfather_name' => 'grandfather_name',
            'father_name' => 'father_name',
            'citizenship_number' => 'citizenship_number',
            'primary_bank_name' => 'primary_bank_name',
            'primary_account_name' => 'primary_account_name',
            'primary_account_number' => 'primary_account_number',
            'primary_bank_branch' => 'primary_bank_branch',
            'secondary_bank_name' => 'secondary_bank_name',
            'secondary_account_name' => 'secondary_account_name',
            'secondary_account_number' => 'secondary_account_number',
            'secondary_bank_branch' => 'secondary_bank_branch',
            'location_type' => 'location_type',
            'payment_type' => 'payment_type',
            'status' => 'status',
            'rental_code' => 'rental_code',
            'termination_clause' => 'termination_clause',
            'location' => 'location',
            'branch_id' => 'branch_id',
            'oc_id' => 'oc_id',
            'rental_type_id' => 'rental_type_id',
        ];
    }
    private function loadOwnerDetails()
    {
        $rentalOwner = RentalOwners::find($this->ownerId);
        $this->rental_code = $rentalOwner->rental_code;
        $this->customer_id = $rentalOwner->customer_id;

        if ($rentalOwner->primary_bank_code) {
            $bank = BankCode::where('code', $rentalOwner->primary_bank_code)->first();
            $this->primary_bank_name = $bank ? $bank->id : null;
        }

        if ($rentalOwner->secondary_bank_code) {
            $secondaryBank = BankCode::where('code', $rentalOwner->secondary_bank_code)->first();
            $this->secondary_bank_name = $secondaryBank ? $secondaryBank->id : null;
        }

        $citizenshipDocument = RentalDocument::where('owner_id', $rentalOwner->citizenship_number)->where('type', 'citizenship')->first();
        $this->existingCitizenshipImage = $citizenshipDocument ? $citizenshipDocument->image_path : null;

        $chequeDocument = RentalDocument::where('owner_id', $rentalOwner->citizenship_number)->where('type', 'cheque')->first();
        $this->existingChequeImage = $chequeDocument ? $chequeDocument->image_path : null;
    }

    private function performSaveOrUpdate($action)
    {
        DB::beginTransaction();
        try {
            $selectedBank = $this->repository->getBankCodes()->where('id', $this->primary_bank_name)->first();
            $this->primary_bank_name = $selectedBank?->name;
            $bankCode =  $selectedBank?->code;

            $selectedSecondaryBank = $this->repository->getBankCodes()->where('id', $this->secondary_bank_name)->first();
            $this->secondary_bank_name = $selectedSecondaryBank?->name;
            $secondaryBankCode = $selectedSecondaryBank?->code;

            $selectedBranch = $this->repository->getBranch()->where('id', $this->branch_id)->first();
            $this->branch_id = $selectedBranch?->id;

            $selectedSubBranch = $this->repository->getSubBranch($this->branch_id)->where('id', $this->oc_id)->first();
            $this->oc_id = $selectedSubBranch?->id;

            $selectedRentalType = $this->repository->getRentalType()->where('id', $this->rental_type_id)->first();
            $this->rental_type_id = $selectedRentalType?->id;

            $data = [];
            foreach ($this->dataField() as $dbField => $propertyName) {
                if (property_exists($this, $propertyName) && isset($this->{$propertyName})) {
                    $data[$dbField] = $this->{$propertyName};
                } else {
                    $data[$dbField] = null;
                }
            }
            $data['primary_bank_code'] = $bankCode;
            $data['secondary_bank_code'] = $secondaryBankCode;
            $data['customer_id'] = $this->customer_id ?: null;

            $message = $this->repository->saveOrUpdateRentalOwner(
                $data,
                $action,
                $this->primary_bank_name,
                $this->secondary_bank_name,
                $this->chequeImage,
                $this->citizenshipImage
            );
            DB::commit();

            $this->notify($message)->send();
            redirect(route('rentalInfo'));
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Exception occurred: ' . $e->getMessage());
            $this->notify("Something went wrong. Please Contact Support.")->type("error")->send();
        }
    }
}
