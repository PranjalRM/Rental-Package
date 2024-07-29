<?php

namespace Pranjal\Rental\Http\Controllers\Rental\Agreement;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use App\Models\Rental\IncrementAmount;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use App\Traits\WithNotify;
use Pranjal\Rental\Models\RentalAgreement;
use Pranjal\Rental\Models\RentalOwners;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Pranjal\Rental\Models\RentalDocument;
use Pranjal\Rental\Models\RentalIncrementDetail;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Pranjal\Rental\Http\Repositories\RentalAgreementRepository;

class AddOwnerAgreement extends Component
{
    use WithFileUploads, WithNotify;

    public $owner;
    public $agreementId;
    public $copyOwnerId;
    public $copyOwnerData;
    public $ownerId;
    public $currentMode="add";

    #[validate('required|nullable')]
    public $incrementForms = [];

    #[validate('required')]
    public $district = '';

    #[validate('required')]
    public $municipality = '';

    #[validate('required')]
    public $place_name = '';

    #[validate('required|integer')]
    public $ward_no = '';

    #[validate('required|integer')]
    public $floors_num = '';

    #[validate('required|integer')]
    public $agreement_floor = '';

    #[validate('required|integer')]
    public $area_floor = '';

    #[validate('required|integer')]
    public $kitta_no = '';

    #[validate('required')]
    public $witnesses = '';

    #[validate('required')]
    public $agreement_date = '';

    public $agreement_end_date = '';

    #[validate('required|integer')]
    public $agreement_period_year = '';

    #[validate('required|numeric|between:1,12|nullable')]
    public $agreement_period_month = '';

    #[validate('required')]
    public $security_deposit = '';

    #[validate('required')]
    public $electricity_rate = '';

    #[validate('required')]
    public $gross_rental_amount = '';
    
    #[validate('required')]
    public $tds_payable = '10';

    public $tds ='';

    public $net_rental_amount = '';

    #[validate('required')]
    public $advance = '';

    #[validate('required')]
    public $payment_period = 'monthly';

    #[validate('required')]
    public $remarks = '';

    public $amendment_child_id= '';

    #[validate('required|mimes:pdf|max:7168')]
    public $agreementDocument= '';

    private RentalAgreementRepository $repository;
    protected $model;

    public function __construct()
    {
        $this->model = new RentalAgreement;
        $this->repository = new RentalAgreementRepository;
    }

    public function mount($ownerId,$copyOwnerId=null)
    {   
        $this->incrementForms[] = [
            'incrementType' => '',
            'increment_percent' => '',
            'increment_amount' => '',
            'increment_after' => '',
            'next_increment_date' => '',
        ];
        if($ownerId){
            $this->currentMode = 'add';
            $this->ownerId = $ownerId;
            $this->owner = RentalOwners::find($ownerId);

        } elseif($copyOwnerId) {
            $this->currentMode = 'copy';
            $copyId =RentalAgreement::find($copyOwnerId);
            $this->copyOwnerId = $copyId->rental_owner_id;
            $this->loadCopyAgreementDetails();  
        }
    }

    private function loadCopyAgreementDetails()
    {
        $agreement = RentalAgreement::with('owner','rentalIncrementDetail', 'file')->where('rental_owner_id',$this->copyOwnerId)->get()->first();
        if ($agreement) {
            $this->owner = $agreement->owner;
            $this->district = $agreement->district;
            $this->municipality = $agreement->municipality;
            $this->place_name = $agreement->place_name;
            $this->ward_no = $agreement->ward_no;
            $this->floors_num = $agreement->floors_num;
            $this->agreement_floor = $agreement->agreement_floor;
            $this->area_floor = $agreement->area_floor;
            $this->kitta_no = $agreement->kitta_no;
            $this->witnesses = $agreement->witnesses;
        }
    }

    public function updated($propertyName,$index)
    {
        if ($propertyName === 'agreement_date' || $propertyName === 'agreement_period_year' || $propertyName === 'agreement_period_month') {
            if (!empty($this->agreement_period_year) || !empty($this->agreement_period_month)) {
                $this->updateAgreementEndDate();
            } else {
                $this->agreement_end_date = null;
            }
        }
        
        if (str_starts_with($propertyName, 'incrementForms')) {
            $segments = explode('.', $propertyName);
            if (count($segments) > 1) {
                $index = $segments[1];
                if (array_key_exists($index, $this->incrementForms)) {
                    $this->updateNextIncrementDate($index);
                }
            }
        }
        if (in_array($propertyName, ['tds_payable', 'gross_rental_amount', 'tds'])) {
            $this->updateNetRentalAmount();
        }
    }
    public function datafield()
    {
        return [ 
            
            'district' => 'district',
            'municipality' =>'municipality',
            'place_name' => 'place_name',
            'ward_no' => 'ward_no',
            'floors_num' => 'floors_num',
            'agreement_floor' => 'agreement_floor',
            'area_floor' => 'area_floor',
            'kitta_no' => 'kitta_no',
            'witnesses' => 'witnesses',
            'agreement_date' => 'agreement_date',
            'agreement_end_date' => 'agreement_end_date',
            'agreement_period_year' => 'agreement_period_year',
            'agreement_period_month' => 'agreement_period_month',
            'security_deposit' =>'security_deposit',
            'electricity_rate' => 'electricity_rate',
            'gross_rental_amount' => 'gross_rental_amount',
            'tds_payable' => 'tds_payable',
            'tds'=> 'tds',
            'net_rental_amount' => 'net_rental_amount',
            'advance' => 'advance',
            'payment_period' => 'payment_period',
            'remarks' =>'remarks',
        ];
    }
    
    public function save()
    {   
        DB::beginTransaction();
        $this->validate();
        try{

            $data=[];
            foreach ($this->dataField() as $dbField => $propertyName) {
                if (property_exists($this, $propertyName) && isset($this->{$propertyName})) {
                    $data[$dbField] = $this->{$propertyName};
                } else {
                    $data[$dbField] = null; 
                }
            }
            $data['rental_owner_id'] = $this->ownerId ?? $this->copyOwnerId;
            $data['amendment_child_id'] = $this->ownerId ?? $this->copyOwnerId;
            $created = $this->repository->saveAgreement($data,$this->agreementDocument);
            $rentalAgreementId = $created->id;

            $this->saveIncrementDetails($rentalAgreementId);
            $this->repository->saveIncrementAmounts($rentalAgreementId, 
                                                    $this->agreement_date,
                                                    $this->agreement_end_date, 
                                                    $this->gross_rental_amount, 
                                                    $this->tds_payable,
                                                    $this->advance);

            redirect(route('agreementInfo',['ownerId' => $this->owner->id]));
            
            DB::commit();
            $message = "Rental agreement saved successfully.";
            $this->notify($message)->send();  
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception occurred: ' . $e->getMessage());
            $this->notify("Something went wrong. Please contact support.")->type("error")->send();
        }
    }

    public function clear() 
    {
        $this->reset();    
    }

    public function addIncrementForm()
    {    if (count($this->incrementForms) < 100) {
            $this->incrementForms[] = [
                'incrementType' => '',
                'increment_percent' => '',
                'increment_amount' => '',
                'increment_after' => '',
                'next_increment_date' => '',
            ];
        }
    }

    public function removeIncrementForm($index)
    {
        unset($this->incrementForms[$index]);
        $this->incrementForms = array_values($this->incrementForms);
    }

    public function render()
    {
        return view('livewire.config.rental.agreement.add-owner-agreement');
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
    
    private function updateAgreementEndDate()
    {
        if ($this->agreement_period_year !== null && $this->agreement_period_month !== null) {
            $englishDate = LaravelNepaliDate::from($this->agreement_date)->toEnglishDate();

            $endDate = Carbon::parse($englishDate)
                ->addYears($this->agreement_period_year)
                ->addMonths($this->agreement_period_month);
            $this->agreement_end_date = LaravelNepaliDate::from($endDate)->toNepaliDate();
        } else {
            $this->agreement_end_date = null;
        }
    }

    private function updateNetRentalAmount()
    {
        $gross_rental_amount = (float) $this->gross_rental_amount;
        $tds_payable = (float) $this->tds_payable;

        $tdsAmount = ($gross_rental_amount * $tds_payable) / 100;
        $this->net_rental_amount = $gross_rental_amount - $tdsAmount;
        $this->tds = $tdsAmount;
    }

    public function updateNextIncrementDate($index)
    {
        if (empty($this->incrementForms)) {
            return;
        }
        $form = $this->incrementForms[$index];

        if (array_key_exists($index, $this->incrementForms)) {
            $englishDate = LaravelNepaliDate::from($this->agreement_date)->toEnglishDate();
            $agreementEndDateEnglish = LaravelNepaliDate::from($this->agreement_end_date)->toEnglishDate();
            $agreementEndDate = Carbon::parse($agreementEndDateEnglish);
            $nextIncrementDate=  Carbon::parse($englishDate)->addYears($form['increment_after']);
            if ($nextIncrementDate < $agreementEndDate){
                $form['next_increment_date'] = LaravelNepaliDate::from($nextIncrementDate)->toNepaliDate();
            } else {
                unset($form['next_increment_date']);
            }
        }
        $this->incrementForms[$index] = $form;
      
    }

    public function saveIncrementDetails($rentalAgreementId)
    {
        foreach ($this->incrementForms as $form) {
            RentalIncrementDetail::create([
                'rental_agreement_id'   => $rentalAgreementId,
                'increment_percent'     => $form['incrementType'] === 'percent' ? $form['increment_percent'] : null,
                'increment_amount'      => $form['incrementType'] === 'amount' ? $form['increment_amount'] : null,
                'increment_after'       => $form['increment_after'] ? $form['increment_after'] : null,
                'next_increment'        => $form['next_increment_date'] ? $form['next_increment_date'] :null,
            ]);  
        }
    }
}    