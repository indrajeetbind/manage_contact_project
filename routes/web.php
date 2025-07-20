<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomFieldController;

Route::get('/', function () {
    return view('home');
});

Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');        // View page
Route::get('/contacts/fetch', [ContactController::class, 'fetch'])->name('contacts.fetch');  // AJAX list
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');       // AJAX create
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy'); // AJAX delete
Route::get('/contacts/{id}/edit', [ContactController::class, 'edit'])->name('contacts.edit'); // AJAX edit
Route::put('/contacts/{id}', [ContactController::class, 'update'])->name('contacts.update'); // AJAX update
Route::post('/contacts/filter', [ContactController::class, 'filter'])->name('contacts.filter'); // AJAX filter

Route::get('/custom-fields', [CustomFieldController::class, 'index'])->name('custom_fields.index'); // View custom fields
Route::post('/save_custom_fields', [CustomFieldController::class, 'store'])->name('custom_fields.store');
// contact merge routes
Route::post('/contacts/merge', [ContactController::class, 'mergeContacts'])->name('contacts.merge');
Route::get('/merged_contacts/{id}', [ContactController::class, 'mergedContacts'])->name('contacts.mergedContacts'); // Show merge form

