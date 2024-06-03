<?php

use Illuminate\Support\Facades\Route;

Route::get('/{number?}', [\App\Http\Controllers\HomeController::class,'index']);

