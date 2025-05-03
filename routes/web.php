<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [VideoController::class, 'create'])->name('dashboard');
    Route::post('/upload', [VideoController::class, 'store'])->name('videos.store');
});

Route::get('/', [VideoController::class, 'index'])->name('home');
Route::get('/video/{video}', [VideoController::class, 'show'])->name('videos.show');


// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
