<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/projects/{directorateId}', [ProjectController::class, 'getByDirectorate']);
