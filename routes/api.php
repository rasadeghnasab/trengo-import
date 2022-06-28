<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('batch/profiles', [\App\Http\Controllers\CompaniesController::class, 'batchStore']);
Route::delete('batch/profiles/purge', [\App\Http\Controllers\CompaniesController::class, 'purgeProfiles']);
Route::delete('batch/contacts/purge', [\App\Http\Controllers\CompaniesController::class, 'purgeContacts']);
