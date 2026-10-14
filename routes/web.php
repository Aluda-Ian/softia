<?php

use App\Http\Controllers\DeployController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('_deploy/{token}')
    ->middleware('throttle:20,1')
    ->name('deploy.')
    ->group(function () {
        Route::get('/', [DeployController::class, 'show'])->name('show');
        Route::post('/run', [DeployController::class, 'run'])->name('run');
        Route::post('/admin', [DeployController::class, 'createAdmin'])->name('admin');
    });
