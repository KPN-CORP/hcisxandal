<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeComparisonController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\LoginController;

/*
|------------------------   --------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- 1. Authentication Routes ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout'); // <-- INI YANG KURANG TADI

// --- 2. Redirect Root to Login or Employee List ---
Route::get('/', function () {
    return auth()->check() ? redirect()->route('employees.list') : redirect()->route('login');
});

// --- 3. Protected Routes (Butuh Login) ---
Route::middleware(['auth'])->group(function () {
    
    // Employee Comparison Routes
        Route::get('/employees/export', [App\Http\Controllers\EmployeeComparisonController::class, 'exportExcel'])->name('employees.export');
    Route::get('/employees', [EmployeeComparisonController::class, 'index'])->name('employees.list');
    Route::get('/employees/chunk', [EmployeeComparisonController::class, 'getDataChunk'])->name('employees.chunk');
    Route::get('/employees/{employeeId}', [EmployeeComparisonController::class, 'show'])->name('employees.detail');
    Route::post('/employees/{employeeId}/confirm', [EmployeeComparisonController::class, 'confirm'])->name('employees.confirm');

    // Role Setting Routes (Resource otomatis buat index, store, update, destroy)
    Route::resource('roles', RoleController::class);
    
    Route::post('/roles/filter-employees', [RoleController::class, 'filterEmployees'])->name('roles.filter_employees');
    
});