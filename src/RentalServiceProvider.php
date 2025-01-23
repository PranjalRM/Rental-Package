<?php

namespace Codebright\Rental;

use Codebright\Rental\Console\Commands\RentalManagementSeeder;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Codebright\Rental\Http\Controllers\Rental\Owner\RentalOwner;
use Codebright\Rental\Http\Controllers\Rental\Owner\AddRental;
use Codebright\Rental\Http\Controllers\Rental\Agreement\UpdateAgreement;
use Codebright\Rental\Http\Controllers\Rental\Agreement\OwnerAgreement;
use Codebright\Rental\Http\Controllers\Rental\Agreement\AddOwnerAgreement;
use Codebright\Rental\Http\Controllers\Rental\Electricity\AddElectricityBill;
use Codebright\Rental\Http\Controllers\Rental\Electricity\EditElectricityBill;
use Codebright\Rental\Http\Controllers\Rental\Electricity\ElectricityBillView;
use Codebright\Rental\Http\Controllers\Rental\Reports\RangeReport;
use Codebright\Rental\Http\Controllers\Rental\Reports\RentalCalculation;
use Codebright\Rental\Http\Controllers\Rental\Reports\RentalReport;
use Codebright\Rental\Http\Controllers\Rental\RentalDashboard;
use Codebright\Rental\Http\Controllers\Rental\ElectricityOwnerDashboard;
use Illuminate\Support\Facades\Log;

class RentalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'rental');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->commands([
            RentalManagementSeeder::class,
        ]);

        Livewire::component('rental-dashboard', RentalDashboard::class);
        Livewire::component('rental-owner', RentalOwner::class);
        Livewire::component('add-rental',   AddRental::class);
        Livewire::component('rental-calculation', RentalCalculation::class);
        Livewire::component('owner-agreement', OwnerAgreement::class);
        Livewire::component('add-owner-agreement', AddOwnerAgreement::class);
        Livewire::component('update-agreement', UpdateAgreement::class);
        Livewire::component('add-electricity-bill', AddElectricityBill::class);
        Livewire::component('electricity-bill', ElectricityBillView::class);
        Livewire::component('edit-electricity-bill', EditElectricityBill::class);
        Livewire::component('electricity-owner-dashboard', ElectricityOwnerDashboard::class);
        Livewire::component('range-report', RangeReport::class);
        Livewire::component('rental-report', RentalReport::class);    
        $this->addStyles();
    }

    public function register() {}

    private function addStyles()
    {
        $this->app->booted(function () {
            $targetPath = resource_path('scss/custom/rental-agreement.scss');

            if (file_exists($targetPath)) {
                try {
                    unlink($targetPath);
                } catch (\Exception $e) {
                    Log::error('Error deleting existing rental-agreement.scss: ' . $e->getMessage());
                }
            }

            $this->publishes([
                __DIR__ . '/resources/scss/rental-agreement.scss' => $targetPath,
            ], 'rental');

            \Illuminate\Support\Facades\Artisan::call('vendor:publish', [
                '--tag' => 'rental'
            ]);

            $appScssPath = resource_path('scss/app.scss');
            $importStatement = '@import "custom/rental-agreement";';

            if (file_exists($appScssPath)) {
                $currentContent = file_get_contents($appScssPath);

                if (stripos($currentContent, $importStatement) === false) {
                    if (substr($currentContent, -1) !== "\n") {
                        $currentContent .= "\n";
                    }

                    $currentContent .= $importStatement . "\n";

                    try {
                        if (file_put_contents($appScssPath, $currentContent) === false) {
                            Log::error('Failed to write to app.scss');
                        } else {
                            Log::info('Successfully added import to app.scss');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error updating app.scss: ' . $e->getMessage());
                    }
                }
            } else {
                Log::error('app.scss does not exist at: ' . $appScssPath);
            }
        });
    }
}
