<?php

use App\Http\Controllers\Api\PriceCalculateController;
use Illuminate\Support\Facades\Route;

Route::post('/calculate', PriceCalculateController::class);