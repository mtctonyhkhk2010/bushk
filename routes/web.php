<?php

use App\Livewire\Offline;
use App\Livewire\Search;
use App\Livewire\ShowInterchange;
use App\Livewire\ShowRoute;
use App\Livewire\ShowServiceTime;

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

Route::get('/', Search::class);
Route::get('/search', Search::class);
Route::get('/route/{route}/{name?}', ShowRoute::class);
Route::get('/stop/{stop}', \App\Livewire\ShowStop::class);
Route::get('/service-time/{route}/{name?}', ShowServiceTime::class);
Route::get('/interchange/{route}/{stop?}', ShowInterchange::class);
Route::get('/favorite-routes', \App\Livewire\FavoriteRoutes::class);
Route::get('/favorite-stops', \App\Livewire\FavoriteStops::class);
Route::get('/offline', Offline::class);
