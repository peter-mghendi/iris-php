<?php

use App\Http\Controllers\VideoController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

Route::view('/', 'welcome');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/chat', fn() => view('chat', ['users' => User::where('id', '<>', Auth::id())->get()]))
        ->name('chat');
        
    Route::post('/token', [VideoController::class, 'token'])->name('token');
    Route::post('/call', [VideoController::class, 'call'])->name('call');
});
