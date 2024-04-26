<?php

use App\Http\Controllers\SaleController;
use App\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SaleController::class, 'index']);
Route::get('/sales/get', [SaleController::class, 'syncData'])->name('sync-data');
Route::get('/project-wise-data/{date}', [SaleController::class, 'projectWiseData'])->name('project-wise-data');
Route::get('/latest-sold-product/{date}', [SaleController::class, 'latestSoldProduct'])->name('latest-sold-product');
Route::get('/latest-refund-product/{date}', [SaleController::class, 'latestRefundProduct'])->name('latest-refund-product');

Route::get('/migrate', [SystemController::class, 'migrate']);
Route::get('/check-db', [SystemController::class, 'checkDB']);
Route::get('/optimize-clear', [SystemController::class, 'optimizeClear']);
