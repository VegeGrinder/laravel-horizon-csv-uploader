<?php

use App\Http\Controllers\CsvFileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [CsvFileController::class, 'index'])->name('csv.index');
Route::get('/csv-rows', [CsvFileController::class, 'getCsvRows'])->name('csv.refreshRows');
Route::post('/upload-csv', [CsvFileController::class, 'uploadCsv'])->name('csv.uploadCsv');
