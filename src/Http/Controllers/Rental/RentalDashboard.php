<?php

namespace Codebright\Rental\Http\Controllers\Rental;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Rental Dashboard')]
class RentalDashboard extends Component
{
    public function render()
    {
        return view('rental::rental.rental-dashboard');
    }

}