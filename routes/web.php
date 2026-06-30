<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
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

Route::get('/', [ContactController::class, 'index'])
    ->name('contacts.index');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])
    ->name('contacts.confirm');
Route::post('/contacts', [ContactController::class, 'store'])
    ->name('contacts.store');
Route::get('/thanks', [ContactController::class, 'thanks'])
    ->name('contacts.thanks');

Route::prefix('admin')
    ->middleware('auth')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])
            ->name('contacts.index');
        Route::get('/contacts/{contact}', [AdminController::class, 'show'])
            ->name('contacts.show');
        Route::delete('/contacts/{contact}', [AdminController::class, 'destroy'])
            ->name('contacts.destroy');
        Route::post('/tags', [TagController::class, 'store'])
            ->name('tags.store');
        Route::get('/tags/{tag}/edit', [TagController::class, 'edit'])
            ->name('tags.edit');
        Route::put('/tags/{tag}', [TagController::class, 'update'])
            ->name('tags.update');
        Route::delete('/tags/{tag}', [TagController::class, 'destroy'])
            ->name('tags.destroy');
    });

Route::get('/contacts/export', [ContactController::class, 'export'])
    ->middleware('auth')
    ->name('contacts.export');
