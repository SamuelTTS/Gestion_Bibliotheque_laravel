<?php

use App\Http\Controllers\ApiBiblioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::apiResource('/apiBib',ApiBiblioController::class);

Route::get('/apiBib/disciplines', [ApiBiblioController::class, 'discipline']);
