<?php

use Illuminate\Support\Facades\Route;

use Pranjal\Rental\Http\Controllers\Rental\RentalOwner;
use Pranjal\Rental\Http\Controllers\Rental\RentalCalculation;
use Pranjal\Rental\Http\Controllers\Rental\AddRental;
use Pranjal\Rental\Http\Controllers\Rental\Agreement\OwnerAgreement;
use Pranjal\Rental\Http\Controllers\Rental\Agreement\AddOwnerAgreement;
use Pranjal\Rental\Http\Controllers\Rental\Agreement\UpdateAgreement;

Route::get('rental', RentalOwner::class)->name('rentalInfo');
Route::get('rental/calculation',RentalCalculation::class)->name('rentalCalculation');
Route::get('rental/add',AddRental::class)->name('addRental');
Route::get('rental/edit/{id}',AddRental::class)->name('editRental');
Route::get('agreement/{ownerId}',OwnerAgreement::class)->name('agreementInfo');
Route::get('agreement/export/{agreementId}',[OwnerAgreement::class, 'export'])->name('exportAgreement');
Route::get('agreement/add/{ownerId}', AddOwnerAgreement::class)->name('addAgreement');
Route::get('agreement/view/{agreementId}', UpdateAgreement::class)->name('viewAgreement');
Route::get('agreement/edit/{agreementEditId}', UpdateAgreement::class)->name('editAgreement');
Route::get('agreement/copy/{copyOwnerId}', AddOwnerAgreement::class)->name('copyAgreement');
