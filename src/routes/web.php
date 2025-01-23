<?php

use Illuminate\Support\Facades\Route;

use Codebright\Rental\Http\Controllers\Rental\Owner\RentalOwner;
use Codebright\Rental\Http\Controllers\Rental\Reports\RentalCalculation;
use Codebright\Rental\Http\Controllers\Rental\Owner\AddRental;
use Codebright\Rental\Http\Controllers\Rental\Agreement\OwnerAgreement;
use Codebright\Rental\Http\Controllers\Rental\Agreement\AddOwnerAgreement;
use Codebright\Rental\Http\Controllers\Rental\Agreement\UpdateAgreement;
use Codebright\Rental\Http\Controllers\Rental\Electricity\AddElectricityBill;
use Codebright\Rental\Http\Controllers\Rental\Electricity\EditElectricityBill;
use Codebright\Rental\Http\Controllers\Rental\Electricity\ElectricityBillView;
use Codebright\Rental\Http\Controllers\Rental\ElectricityOwnerDashboard;
use Codebright\Rental\Http\Controllers\Rental\RentalDashboard;
use Maatwebsite\Excel\Facades\Excel;
use Codebright\Rental\Exports\SummaryReportExport;
use Codebright\Rental\Http\Controllers\Rental\Reports\RangeReport;
use Codebright\Rental\Http\Controllers\Rental\Reports\RentalReport;

Route::middleware(['auth','web'])->group (function (){
    Route::get('rentalDashboard', RentalDashboard::class)->name('rentalDashboard');
    Route::get('billing/rental', ElectricityOwnerDashboard::class)->name('billingDashboard');
    Route::get('rental/calculation',RentalCalculation::class)->name('rentalCalculation');
    Route::get('rentalDashboard/range_report',RangeReport::class)->name('rangeReport');
    Route::get('rental', RentalOwner::class)->name('rentalInfo');
    Route::get('rental/add',AddRental::class)->name('addRental');
    Route::get('rental/edit/{id}',AddRental::class)->name('editRental');
    Route::get('agreement/{ownerId}',OwnerAgreement::class)->name('agreementInfo');
    Route::get('agreement/export/{agreementId}',[OwnerAgreement::class, 'export'])->name('exportAgreement');
    Route::get('agreement/add/{ownerId}', AddOwnerAgreement::class)->name('addAgreement');
    Route::get('agreement/view/{agreementId}', UpdateAgreement::class)->name('viewAgreement');
    Route::get('agreement/edit/{agreementEditId}', UpdateAgreement::class)->name('editAgreement');
    Route::get('agreement/copy/{copyOwnerId}', AddOwnerAgreement::class)->name('copyAgreement');
    Route::get('rental/{ownerId}/electricity-bill/view',ElectricityBillView::class)->name('viewElectricityBill');
    Route::get('rental/{ownerId}/electricity-bill/add', AddElectricityBill::class)->name('addElectricityBill');
    Route::get('rental/{ownerId}/electricity-bill/{billId}/edit', EditElectricityBill::class)->name('editElectricityBill');
    Route::get('/download-summary-report', function () {
        return Excel::download(new SummaryReportExport, 'RentalSummaryReport.xlsx');
    })->name('download.summary.report');
    Route::get('rentalDashboard/report', RentalReport::class)->name('rentalReport');
});
