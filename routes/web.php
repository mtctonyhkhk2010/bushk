<?php

use App\Livewire\Search;
use App\Livewire\ShowRoute;
use App\Livewire\Welcome;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/search');
});
Route::get('/search', Search::class);
Route::get('/route/{route}/{name?}', ShowRoute::class);
