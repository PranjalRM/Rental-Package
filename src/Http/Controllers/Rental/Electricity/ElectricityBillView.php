<?php

namespace Codebright\Rental\Http\Controllers\Rental\Electricity;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use App\Traits\WithDataTable;
use Carbon\Carbon;
use Codebright\Rental\Models\ElectricityBills;
use Codebright\Rental\Models\RentalOwners;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Http\UploadedFile;

#[Title('Electricity Bill')]
class ElectricityBillView extends Component
{
    use WithDataTable, WithFileUploads;

    public $ownerId, $owner;

    public $selectAllData = false;
    public $selectedRows = [];
    public $addBill = false;

    #[Validate('required|file|mimes:jpeg,png,jpg|max:2048')]
    public $receiptFile;

    public function mount($ownerId)
    {
        $today = Carbon::now();
        $currentDate = LaravelNepaliDate::from($today)->toNepaliDate();
        [$currentYear, $currentMOnth, $currentDay] = explode('-', $currentDate);
        if($currentDay >= 20 && $currentDay <= 25)
        {
            $this->addBill = true;
        }
        $this->ownerId = $ownerId;
        $this->owner = RentalOwners::find($ownerId);
    }

    #[Computed(persist: true)]
    public function list()
    {
        $list = ElectricityBills::whereHas('agreementOwner.owner', function ($query) {
            $query->where('rental_owner_id', $this->ownerId)
                ->where('rental_status', 'Approved');
        })
            ->with('agreementOwner')
            ->orderByDesc('created_at');
        $list = $this->applySorting($list)->paginate($this->perPage);;
        return $list;
    }

    public function edit(ElectricityBills $bill)
    {
        return redirect()->route('editElectricityBill', ['ownerId' => $bill->agreementOwner->rental_owner_id, 'billId' => $bill]);
    }

    public function uploadReciept(ElectricityBills $bill)
    {
        $this->validate();
        $billReciept = ElectricityBills::where('id', $bill->id)->first();
        if ($this->receiptFile instanceof UploadedFile) {
            if ($billReciept->receipt_upload_image) {
                Storage::delete('public/' . $billReciept->receipt_upload_image);
            }
            $filePath = $this->saveDocument($this->receiptFile);
            $billReciept->receipt_upload_image = $filePath;
            $billReciept->status_req = 'paid';
            $billReciept->save();

            $this->receiptFile = null;
            $this->dispatch('hide-model');
            $message = "Document Uploaded successfully.";
            unset($this->list);
            $this->notify($message)->send();
        } else {
            throw new \Exception("No valid file uploaded");
        }
    }

    private function saveDocument($file)
    {
        if ($file instanceof UploadedFile) {
            $filePath = $file->store('public/documents/electricty-bill');
            $filePath = str_replace('public/', '', $filePath);
            return $filePath;
        } elseif (is_string($file)) {
            return $file;
        }

        return null;
    }

    public function updatedSelectAllData()
    {
        if ($this->selectAllData) {
            $this->selectedRows = $this->list->where('status', 'Pending')->pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function markSelectedAsPaid()
    {
        if (count($this->selectedRows) > 0) {
            $bills = ElectricityBills::whereIn('id', $this->selectedRows)->get();
            foreach ($bills as $bill) {
                $bill->status_req = 'paid';
                $bill->save();
            }
            $message = "Selected bills marked as paid successfully.";
            unset($this->list);
            $this->notify($message)->send();
        } else {
            $this->notify('No bills selected')->type('error')->send();
        }
    }
    public function render()
    {
        return view('rental::rental.electricity.electricity-bill-view');
    }
}
