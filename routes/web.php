<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\livre_controller;
use App\Http\Controllers\discipline_controller;
use App\Http\Controllers\Login_Controller;
use Illuminate\Support\Facades\Redirect;

// Route::get('/', function () {
//     return Redirect::route('login');
// });

Route::get('/livres', [livre_controller::class, 'index'])->name('alllivres');

Route::get('/formlivre', [livre_controller::class, 'create'])->name('formlivre');

Route::post('/formlivre', [livre_controller::class, 'store'])->name('storelivre');

Route::get('/livres/delete/{id}', [livre_controller::class, 'destroy'])->name('deletelivre');

Route::get('/livres/updateform/{id}', [livre_controller::class, 'edit'])->name('updateform');

Route::post('/livres/update', [livre_controller::class, 'update'])->name('updatelivre');

Route::post('/livres/find', [livre_controller::class, 'find'])->name('findlivre');

Route::get('/discipline', [discipline_controller::class, 'create'])->name('adddisc');

Route::post('/discipline', [discipline_controller::class, 'store'])->name('storedisci');

Route::get('/',[Login_Controller::class, 'index'])->name('login');

Route::post('/login',[Login_Controller::class, 'connexion'])->name('loginuser');

Route::get('/register',[Login_Controller::class, 'create'])->name('formregister');

Route::post('/register',[Login_Controller::class, 'store'])->name('register');

Route::get('/logout', [Login_Controller::class, 'logout'])->name('logout');
