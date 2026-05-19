<?php

use App\Http\Controllers\Api\TrackController;
use Illuminate\Support\Facades\Route;

Route::post('/track', TrackController::class)
    ->middleware('throttle:60,1');
