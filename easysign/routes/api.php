<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\RapportController;



// ----------------------------
// AUTH
// ----------------------------
Route::post('/register-superadmin', [UserController::class, 'registerSuperadmin']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/organisation-store', [OrganisationController::class, 'store']); // créer organisation
// Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.verify');

// ----------------------------
// ROUTES PROTÉGÉES (auth:sanctum)
// ----------------------------
Route::middleware('auth:sanctum')->group(function () {

    // USER / ADMIN
    Route::get('/user', [UserController::class, 'me']);
    Route::post('/add-admin', [UserController::class, 'addAdmin']);
    Route::get('/list-admins', [UserController::class, 'listAdmins']);
    Route::put('/update-admin/{id}', [UserController::class, 'updateAdmin']);
    Route::delete('/delete-admin/{id}', [UserController::class, 'deleteAdmin']);
    Route::post('/logout', [UserController::class, 'logout']);

    // ORGANISATION (superadmin uniquement)

    Route::get('/organisation', [OrganisationController::class, 'show']); // détails organisation
    Route::put('/organisation', [OrganisationController::class, 'update']); // modifier organisation

    // HORAIRES
    Route::post('/horaire', [OrganisationController::class, 'addHoraire']); // ajouter horaire
    Route::get('/horaires', [OrganisationController::class, 'horaires']); // lister horaires
    Route::delete('/horaire/{id}', [OrganisationController::class, 'deleteHoraire']); // supprimer horaire

    // PERSONNEL
    Route::post('/personnel', [PersonnelController::class, 'store']); // créer personnel
    Route::get('/personnel', [PersonnelController::class, 'index']); // liste personnel
    Route::get('/personnel/{id}', [PersonnelController::class, 'show']); // détails personnel
    Route::put('/personnel/{id}', [PersonnelController::class, 'update']); // modifier personnel
    Route::delete('/personnel/{id}', [PersonnelController::class, 'destroy']); // supprimer personnel
    Route::get('/personnel/{id}/qrcode', [PersonnelController::class, 'generateQrImage']); // QR code SVG

    // PRESENCE / EMARGEMENT
    Route::post('/emargement', [PresenceController::class, 'emargement']); // émargement QR
    Route::get('/presences/today', [PresenceController::class, 'today']); // présences du jour
    Route::get('/presences/{personnelId}/history', [PresenceController::class, 'history']); // historique personnel

    // RAPPORTS
    Route::get('/rapport/journalier', [RapportController::class, 'journalier']); // rapport journalier
    // Optionnel : ajout futur pour mensuel, annuel etc.
});
