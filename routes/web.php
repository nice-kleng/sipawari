<?php

use App\Http\Controllers\PublicRatingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public rating routes (no authentication required)
Route::prefix('rate')->name('public.rate.')->group(function () {
    Route::get('/{uuid}', [PublicRatingController::class, 'show'])
        ->name('show');

    Route::post('/{uuid}', [PublicRatingController::class, 'store'])
        ->name('store');

    Route::get('/{uuid}/success', [PublicRatingController::class, 'success'])
        ->name('success');
});

// Redirect root to admin panel
Route::redirect('/', '/admin');
