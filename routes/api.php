<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShelterController;
use App\Http\Controllers\HelpRequestController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\TransportRequestController;
use App\Http\Controllers\AreaAssignmentController;
use App\Http\Controllers\SecondaryShelterController;

/*
|--------------------------------------------------------------------------
| SHELTERS (MAIN + SECONDARY)
|--------------------------------------------------------------------------
*/

// MAIN SHELTERS
Route::get('/shelters', [ShelterController::class, 'index']);
Route::get('/shelters/nearby', [ShelterController::class, 'nearby']);
Route::get('/shelters/{id}', [ShelterController::class, 'show']);

// SECONDARY SHELTERS (Gathering Points)
Route::get('/secondary-shelters', [SecondaryShelterController::class, 'index']);
Route::get(
    '/secondary-shelters/by-neighborhood',
    [SecondaryShelterController::class, 'byNeighborhood']
);

/*
|--------------------------------------------------------------------------
| HELP REQUESTS
|--------------------------------------------------------------------------
*/

Route::post('/help-requests', [HelpRequestController::class, 'store']);
Route::get('/help-requests', [HelpRequestController::class, 'index']);
Route::get('/help-requests/filter', [HelpRequestController::class, 'filter']);
Route::get('/help-requests/{id}', [HelpRequestController::class, 'show']);
Route::get('/help-requests/{id}/details', [HelpRequestController::class, 'details']);
Route::put('/help-requests/{id}', [HelpRequestController::class, 'update']);

/*
|--------------------------------------------------------------------------
| CARDS & MEMBERS
|--------------------------------------------------------------------------
*/

// MEMBERS (üstte kalmalı – doğru yapmışsın 👍)
Route::put(
    '/cards/{card_id}/members/{member_id}/status',
    [CardController::class, 'updateMemberStatus']
);

Route::put(
    '/cards/{card_id}/members/{member_id}/location',
    [CardController::class, 'updateMemberLocation']
);

// CARDS
Route::post('/cards', [CardController::class, 'store']);
Route::get('/cards/{id}', [CardController::class, 'show']);
Route::get('/cards/{id}/status', [CardController::class, 'status']);
Route::put('/cards/{id}', [CardController::class, 'update']);
Route::post('/cards/{id}/add-balance', [CardController::class, 'addBalance']);
Route::post('/cards/{id}/spend', [CardController::class, 'spend']);
Route::get('/cards/qr/{qr}', [CardController::class, 'showByQr']);
Route::post('/cards/{id}/members', [CardController::class, 'addMember']);
Route::delete(
    '/cards/{card_id}/members/{member_id}',
    [CardController::class, 'deleteMember']
);

/*
|--------------------------------------------------------------------------
| TRANSPORT & AREA ASSIGNMENTS
|--------------------------------------------------------------------------
*/

Route::post('/transport-requests', [TransportRequestController::class, 'store']);
Route::get('/transport-requests', [TransportRequestController::class, 'index']);
Route::put('/transport-requests/{id}', [TransportRequestController::class, 'update']);

Route::post('/area-assignments', [AreaAssignmentController::class, 'store']);
