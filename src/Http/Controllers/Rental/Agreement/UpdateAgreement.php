<?php

namespace CodeBright\Rental\Http\Controllers\Rental\Agreement;

use CodeBright\Rental\Models\RentalAgreement;
use CodeBright\Rental\Models\RentalIncrementDetail;
use App\Traits\WithNotify;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use CodeBright\Rental\Http\Repositories\RentalAgreementRepository;
use CodeBright\Rental\Models\RentalDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;

#[Title('Update Agreement')]
class UpdateAgreement extends Component
{
    use WithFileUploads, WithNotify;

    public $agreement;
    public $owner;
    public $editMode = false;

    public $agreementId;
    #[validate('required|nullable')]
    public $incrementForms = [];

    #[validate('required|string')]
    public $district = '';

    #[validate('required|string')]
    public $municipality = '';

    #[validate('required|string')]
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
    
    public $tds='';
    public $net_rental_amount = '';

    #[validate('required')]
    public $advance = '';

    #[validate('required')]
    public $payment_period = 'monthly';

    #[validate('required')]
    public $remarks = '';

    #[validate('required|mimes:pdf|max:7168')]
    public $agreementDocument = '';

    public $existingAgreementDocument = '';
    protected $model;
    private RentalAgreementRepository $repository;

    public function __construct()
    {
        $this->model = new RentalAgreement;
        $this->repository = new RentalAgreementRepository;
    }

    public function mount($agreementId, $agreementEditId = null)
    {
        if ($agreementEditId) {
                $this->editMode = true;
                $this->agreementId = $agreementEditId;
                $this->loadAgreementDetails();
                $this->fillFormModel($agreementEditId);
        }elseif($agreementId){
            $this->loadAgreementDetails();
            $this->fillformModel($agreementId);
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
    public function back()
    {
        return redirect()->route('agreementInfo', ['ownerId' => $this->agreement->rental_owner_id]);
    }
    private function loadAgreementDetails()
    {
        $this->agreement = RentalAgreement::with('rentalIncrementDetail', 'file')->find($this->agreementId);
        if ($this->agreement) {
            $this->owner = $this->agreement->owner;

            $netIncrement = $this->agreement->rentalIncrementDetail;
            if ($netIncrement) {
                foreach ($netIncrement as $increment) {
                    $this->incrementForms[] = [
                        'incrementType' => $increment->increment_percent !== null ? 'percent' : 'amount',
                        'increment_percent' => $increment->increment_percent,
                        'increment_amount' => $increment->increment_amount, 
                        'increment_after' => $increment->increment_after,
                        'next_increment_date' => $increment->next_increment,
                    ];
                }
            }
            if (empty($this->incrementForms)) {
                $this->incrementForms[] = [
                    'incrementType' => '',
                    'increment_percent' => '',
                    'increment_amount' => '',
                    'increment_after' => '',
                    'next_increment_date' => '',
                ];
            }
            $document = RentalDocument::where('agreement_id', $this->agreementId)->where('type', 'agreement')->first();
            $this->existingAgreementDocument  = $document ? $document->image_path : null;
        }
    }

    private function dataField()
    {
        return [
            'id' => 'agreementId',
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
        try {
            $data=[];
            foreach ($this->dataField() as $dbField => $propertyName) {
                if (property_exists($this, $propertyName) && isset($this->{$propertyName})) {
                    $data[$dbField] = $this->{$propertyName};
                } else {
                    $data[$dbField] = null; 
                }
            }
            $message = $this->repository->updateAgreement($data ,$this->agreementDocument);
          
            $this->saveIncrementDetails($this->agreement->id);
        
            $this->repository->updateIncrementAmounts($this->agreement->id, 
                                                    $this->agreement_date,
                                                    $this->agreement_end_date, 
                                                    $this->gross_rental_amount, 
                                                    $this->tds_payable,
                                                    $this->advance);
        
            $this->editMode = false;
            DB::commit();
            $this->notify($message)->send();
            return redirect()->route('agreementInfo', ['ownerId' => $this->agreement->rental_owner_id]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception Ocuured: ' . $e->getMessage());
            $this->notify("Something went wrong. Please Contact Support.")->type("error")->send();
        }
    }

    private function saveIncrementDetails($rentalAgreementId)
    {
        $existingDetails = RentalIncrementDetail::where('rental_agreement_id', $rentalAgreementId)->first();

        RentalIncrementDetail::where('rental_agreement_id', $rentalAgreementId)->delete();
        foreach ($this->incrementForms as $form) {
            $incrementType = isset($form['incrementType']) ? $form['incrementType'] : null;
            $incrementPercent = isset($form['increment_percent']) ? $form['increment_percent'] : null;
            $incrementAmount = isset($form['increment_amount']) ? $form['increment_amount'] : null;
            $incrementAfter = isset($form['increment_after']) ? $form['increment_after'] : null;
            $nextIncrementDate = isset($form['next_increment_date']) ? $form['next_increment_date'] : null;
            
            $existingNextIncrement = $existingDetails ? $existingDetails->next_increment : null;
            $existingIncrementAfter = $existingDetails ? $existingDetails->increment_after : null;
            
            RentalIncrementDetail::create([
                'rental_agreement_id' => $rentalAgreementId,
                'increment_percent' => $incrementType === 'percent' ? $incrementPercent : null,
                'increment_amount' => $incrementType === 'amount' ? $incrementAmount : null,
                'increment_after' => $incrementAfter ? ($incrementAfter ? : $existingIncrementAfter) :null,
                'next_increment' => $nextIncrementDate ? ($nextIncrementDate ? :$existingNextIncrement) :null,
            ]);
        }
    }

    public function updated($propertyName, $index)
    {
        if (in_array($propertyName, ['agreement_date', 'agreement_period_year', 'agreement_period_month'])) {
            $this->updateAgreementEndDate();
        }

        if (in_array($propertyName, ['tds_payable', 'gross_rental_amount'])) {
            $this->updateNetRentalAmount();
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
    }

    private function updateAgreementEndDate()
    {
        if ($this->agreement_date && $this->agreement_period_year !== null && $this->agreement_period_month !== null) {
            $englishDate = LaravelNepaliDate::from($this->agreement_date)->toEnglishDate();

            $endDate = Carbon::parse($englishDate)
                ->addYears($this->agreement_period_year)
                ->addMonths($this->agreement_period_month);
            $this->agreement_end_date = LaravelNepaliDate::from($endDate)->toNepaliDate();
        }
    }

    private function updateNetRentalAmount()
    {
        $tdsAmount = ($this->gross_rental_amount * $this->tds_payable) / 100;
        $this->net_rental_amount = $this->gross_rental_amount - $tdsAmount;
        $this->tds = $tdsAmount;

    }

    private function updateNextIncrementDate($index)
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

    public function addIncrementForm()
    {
        if (count($this->incrementForms) < 100) {
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
        return view('rental::rental.agreement.update-agreement');
    }
}
