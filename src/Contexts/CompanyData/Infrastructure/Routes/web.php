<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── CompanyData CRUD (Inertia pages) ──
Route::prefix('company-data')->middleware(['web', 'auth'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('company-data/CompanyDataIndexPage');
    })->name('company-data.index');

    Route::get('/create', function () {
        return Inertia::render('company-data/CompanyDataCreatePage');
    })->name('company-data.create');

    Route::get('/{uuid}', function (string $uuid) {
        return Inertia::render('company-data/CompanyDataShowPage', ['companyId' => $uuid]);
    })->name('company-data.show')->whereUuid('uuid');

    Route::get('/{uuid}/edit', function (string $uuid) {
        return Inertia::render('company-data/CompanyDataEditPage', ['companyId' => $uuid]);
    })->name('company-data.edit')->whereUuid('uuid');
});
