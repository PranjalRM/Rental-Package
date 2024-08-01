<?php

namespace CodeBright\Rental;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use CodeBright\Rental\Http\Controllers\Rental\RentalOwner;
use CodeBright\Rental\Http\Controllers\Rental\AddRental;
use CodeBright\Rental\Http\Controllers\Rental\RentalCalculation;
use CodeBright\Rental\Http\Controllers\Rental\Agreement\UpdateAgreement;
use CodeBright\Rental\Http\Controllers\Rental\Agreement\OwnerAgreement;
use CodeBright\Rental\Http\Controllers\Rental\Agreement\AddOwnerAgreement;
class RentalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__. '/routes/web.php');
        $this->loadViewsFrom(__DIR__. '/views', 'rental');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        Livewire::component('rental-owner', RentalOwner::class);
        Livewire::component('add-rental',   AddRental::class);
        Livewire::component('rental-calculation', RentalCalculation::class);
        Livewire::component('owner-agreement', OwnerAgreement::class);
        Livewire::component('add-owner-agreement', AddOwnerAgreement::class);
        Livewire::component('update-agreement', UpdateAgreement::class);
    }

    public function register()
    {

    }
}