<?php

namespace Pranjal\Rental\Http\Controllers\Rental;

use App\Traits\WithDataTable;
use Livewire\Component;
use Livewire\Attributes\Title;
use Pranjal\Rental\Models\RentalOwners;
use App\Traits\WithNotify;
use Livewire\Attributes\Validate;
use Pranjal\Rental\Models\RentalReject;
use Livewire\Attributes\Computed;
use App\Models\Employee\Employee;
use Illuminate\Support\Facades\Auth;

#[Title('Rental Owner')]
class RentalOwner extends Component
{
    use WithDataTable,WithNotify;

    public $display;
    
    #[Validate('string')]
    public $reason;

    #[Computed(persist:true)]
    public function list()
    {
        $list = RentalOwners::with('branch', 'documents', 'agreementStatus')->search($this->search)->orderByDesc('created_at');
        $list = $this->applySorting($list)->paginate($this->perPage);;
        return $list;
    }
 
    public function approve($ownerId)
    {
        $owner = RentalOwners::findOrFail($ownerId);
        $owner->status = '11';
        $owner->rental_status='Approved';
        $owner->save();

        $authenticatedUserId = Auth::id();
        $employee = Employee::where('user_id', $authenticatedUserId)->first();
        if ($employee) {
            $owner->approved_by = $employee->id;
            $owner->save();
        }
        $message = "Post Updated Successfully";
        $this->notify($message)->send();
        unset($this->list);
    }

    public function edit(RentalOwners $id)
    {
        return redirect()->route('editRental',['id' => $id]);
    }

    public function delete(RentalOwners $id)
    {
            $id -> delete();
            $message = 'Rental owner deleted successfully';
            unset($this->list);
            $this->notify($message)->send();
    }

    public function addReason($ownerId)
    {
        $this->validate(['reason' => 'required|string|min:5']);
        $owner = RentalOwners::findOrFail($ownerId);
        
        $reject = new RentalReject();
        $reject->reason = $this->reason;
        $reject->owner_id = $owner->id; 
        $reject->save();

        $owner->status = 'Rejected';
        $owner->rental_status='Rejected';
        $owner->save();

        $this->dispatch('hide-model');
        $this->notify('Reason added successfully')->send();
        unset($this->list); 
        $this->reason = '';
    }

    public function agreement(RentalOwners $ownerId)
    {
        return redirect()->route('agreementInfo',['ownerId' => $ownerId]);
    }
    public function render()
    {   
        return view('livewire.config.rental.rental-owner');
    }
}
