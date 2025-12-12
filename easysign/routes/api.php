<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\RapportController;

Route::post('/register-superadmin', [AuthController::class, 'registerSuperadmin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/add-admin', [AuthController::class, 'addAdmin']);

    Route::post('/organisation', [OrganisationController::class, 'store']);
    Route::post('/horaire', [OrganisationController::class, 'addHoraire']);

    Route::post('/personnel', [PersonnelController::class, 'store']);

    Route::post('/emargement', [PresenceController::class, 'emargementBiometrique']);

    Route::get('/rapport/journalier', [RapportController::class, 'journalier']);
});
