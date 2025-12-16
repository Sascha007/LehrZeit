<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BillingPeriodController;
use App\Http\Controllers\TeachingSessionController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'welcome'])->name('welcome');

Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Billing Periods
    Route::resource('billing-periods', BillingPeriodController::class)->except(['edit', 'update']);
    Route::post('/billing-periods/{billingPeriod}/submit', [BillingPeriodController::class, 'submit'])->name('billing-periods.submit');

    // Teaching Sessions
    Route::resource('teaching-sessions', TeachingSessionController::class)->except(['index', 'show']);

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['index', 'show']);
    Route::get('/expenses/{expense}/download-receipt', [ExpenseController::class, 'downloadReceipt'])->name('expenses.download-receipt');

    // Export
    Route::get('/billing-periods/{billingPeriod}/export/csv', [ExportController::class, 'exportCsv'])->name('billing-periods.export.csv');
    Route::get('/billing-periods/{billingPeriod}/export/xlsx', [ExportController::class, 'exportXlsx'])->name('billing-periods.export.xlsx');

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::post('/billing-periods/{billingPeriod}/approve', [AdminController::class, 'approve'])->name('billing-periods.approve');
        Route::post('/billing-periods/{billingPeriod}/reopen', [AdminController::class, 'reopen'])->name('billing-periods.reopen');
        Route::post('/billing-periods/{billingPeriod}/mark-exported', [AdminController::class, 'markExported'])->name('billing-periods.mark-exported');
    });
});

require __DIR__.'/auth.php';
