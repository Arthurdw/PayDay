<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\SourceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::get('/me', [AuthController::class, 'getMe']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::put('/sources', [SourceController::class, 'create']);
Route::get('/sources', [SourceController::class, 'getAll']);
Route::get('/sources/all', [SourceController::class, 'getAllPairs']);
Route::get('/sources/{id}', [SourceController::class, 'get']);
Route::patch('/sources/{id}', [SourceController::class, 'update']);
Route::delete('/sources/{id}', [SourceController::class, 'delete']);

Route::get('/balances', [BalanceController::class, 'getBalance']);
Route::post('/balances', [BalanceController::class, 'add']);
Route::delete('/balances/{id}', [BalanceController::class, 'delete']);

Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
Route::get('/analytics/overview/{id}', [AnalyticsController::class, 'overviewOf']);
Route::get('/analytics/statistics', [AnalyticsController::class, 'statistics']);
Route::get('/analytics/statistics/{id}', [AnalyticsController::class, 'statisticsOf']);


