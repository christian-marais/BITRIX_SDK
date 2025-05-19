<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

// Route::prefix('{locale}')->group(function () {
    // $request = Request::capture();
    // $request->setLocale(explode('/', $request->path())[0]);
   
// });
    
    Route::get('/', function (Request $request) {
        return view('welcome');
    })->name('welcome');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
        Route::get('/upload', [FileUploadController::class, 'index'])->name('upload.index');
        Route::post('/upload', [FileUploadController::class, 'store'])->name('upload.store');
    });

    require __DIR__.'/auth.php';


