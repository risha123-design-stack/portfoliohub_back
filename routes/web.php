<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicPortfolioController;

Route::get('/', function () {
    return view('welcome');
});
Route::get(
    '/share/{slug}',
    [PublicPortfolioController::class, 'share']
);
