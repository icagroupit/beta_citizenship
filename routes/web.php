<?php

use App\Http\Controllers\MockTestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [MockTestController::class, 'show'])->name('mock-test.list');
Route::get('/start-mock-test/{slug}', [MockTestController::class, 'start'])->name('start.mock-test');
Route::post('/mock-test/{slug}/submit', [MockTestController::class, 'submitAnswer'])->name('submit.answer');
Route::get('/mock-test/{slug}/prepare', [MockTestController::class, 'prepare'])->name('mock-test.prepare');
Route::get('/mock-test/result', [MockTestController::class, 'showResult'])->name('mock-test.result');
