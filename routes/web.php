<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PAGE D'ACCUEIL
// ==========================================

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ==========================================
// ROUTES PROTÉGÉES
// ==========================================

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [
        DashboardController::class,
        'index',
    ])->name('dashboard');

    Route::get('/dashboard/sales-chart', [
        DashboardController::class,
        'salesChart',
    ])->name('dashboard.sales-chart');

    Route::get('/search', [SearchController::class, 'index'])
        ->name('search.index');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('can:viewAny,App\Models\Sale')
        ->middleware('can:viewAny,App\Models\Purchase')
        ->middleware('can:viewAny,App\Models\Expense');

    Route::get('/reports/export', [ReportController::class, 'export'])
        ->name('reports.export')
        ->middleware('can:viewAny,App\Models\Sale')
        ->middleware('can:viewAny,App\Models\Purchase')
        ->middleware('can:viewAny,App\Models\Expense');

    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->name('invoices.index')
        ->middleware('can:viewAny,App\Models\Invoice');

    Route::get('/sales/{sale}/invoice/create', [
        InvoiceController::class,
        'create',
    ])
        ->name('sales.invoices.create')
        ->middleware('can:create,App\Models\Invoice');

    Route::post('/sales/{sale}/invoice', [
        InvoiceController::class,
        'store',
    ])
        ->name('sales.invoices.store')
        ->middleware('can:create,App\Models\Invoice');

    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->name('invoices.show')
        ->middleware('can:view,invoice');

    Route::patch('/invoices/{invoice}/payment-status', [
        InvoiceController::class,
        'updatePaymentStatus',
    ])
        ->name('invoices.payment-status')
        ->middleware('can:update,invoice');

    Route::get('/invoices/{invoice}/pdf', [
        InvoiceController::class,
        'pdf',
    ])
        ->name('invoices.pdf')
        ->middleware('can:view,invoice');

    Route::post('/invoices/{invoice}/send', [
        InvoiceController::class,
        'send',
    ])
        ->name('invoices.send')
        ->middleware('can:update,invoice');

    Route::post('/invoices/{invoice}/payments', [
        InvoiceController::class,
        'storePayment',
    ])
        ->name('invoices.payments.store')
        ->middleware('can:update,invoice');

    // categories
    Route::resource('categories', CategoryController::class)
        ->except(['show'])
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\Category'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\Category'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,category'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,category'
        );

    // Produits
    Route::resource('products', ProductController::class)
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\Product'
        )
        ->middlewareFor(
            ['show'],
            'can:view,product'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\Product'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,product'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,product'
        );

    // clients

    Route::resource('clients', ClientController::class)
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\Client'
        )
        ->middlewareFor(
            ['show'],
            'can:view,client'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\Client'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,client'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,client'
        );

    // ventes

    Route::resource('sales', SaleController::class)
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\Sale'
        )
        ->middlewareFor(
            ['show'],
            'can:view,sale'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\Sale'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,sale'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,sale'
        );

    Route::post('/sales/{sale}/complete', [SaleController::class, 'complete'])
        ->name('sales.complete')
        ->middleware('can:update,sale');

    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])
        ->name('sales.cancel')
        ->middleware('can:update,sale');

    // depenses

    Route::resource('expenses', ExpenseController::class)
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\Expense'
        )
        ->middlewareFor(
            ['show'],
            'can:view,expense'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\Expense'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,expense'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,expense'
        );

    Route::resource('expense-categories', ExpenseCategoryController::class)
        ->except(['show'])
        ->middlewareFor(['index'], 'can:viewAny,App\Models\ExpenseCategory')
        ->middlewareFor(['create', 'store'], 'can:create,App\Models\ExpenseCategory')
        ->middlewareFor(['edit', 'update'], 'can:update,expense_category')
        ->middlewareFor(['destroy'], 'can:delete,expense_category');

    // Mouvements de stock
    Route::resource(
        'stock-movements',
        StockMovementController::class
    )
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\StockMovement'
        )
        ->middlewareFor(
            ['show'],
            'can:view,stock_movement'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\StockMovement'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,stock_movement'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,stock_movement'
        );

    // Fournisseurs

    Route::resource('suppliers', SupplierController::class)
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\Supplier'
        )
        ->middlewareFor(
            ['show'],
            'can:view,supplier'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\Supplier'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,supplier'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,supplier'
        );

    // Achats

    Route::resource('purchases', PurchaseController::class)
        ->middlewareFor(
            ['index'],
            'can:viewAny,App\Models\Purchase'
        )
        ->middlewareFor(
            ['show'],
            'can:view,purchase'
        )
        ->middlewareFor(
            ['create', 'store'],
            'can:create,App\Models\Purchase'
        )
        ->middlewareFor(
            ['edit', 'update'],
            'can:update,purchase'
        )
        ->middlewareFor(
            ['destroy'],
            'can:delete,purchase'
        );

    Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])
        ->name('purchases.receive')
        ->middleware('can:update,purchase');

    Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])
        ->name('purchases.cancel')
        ->middleware('can:update,purchase');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

});

Route::middleware('auth')->group(function () {
    Route::post('/company/join-code', [UserController::class, 'issueJoinCode'])
        ->name('company.join-code')
        ->middleware('can:viewAny,App\Models\User');

    Route::resource('users', UserController::class)
        ->middlewareFor(['index'], 'can:viewAny,App\Models\User')
        ->middlewareFor(['show'], 'can:view,user')
        ->middlewareFor(['create', 'store'], 'can:create,App\Models\User')
        ->middlewareFor(['edit', 'update'], 'can:update,user')
        ->middlewareFor(['destroy'], 'can:delete,user');
});
