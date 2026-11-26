<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim($validated['q'] ?? '');
        $pattern = '%'.$term.'%';

        $products = collect();
        $clients = collect();
        $suppliers = collect();
        $sales = collect();
        $purchases = collect();
        $invoices = collect();
        $expenses = collect();

        if ($term !== '') {
            $products = Product::query()
                ->where(function ($query) use ($pattern) {
                    $query->where('name', 'like', $pattern)
                        ->orWhere('reference', 'like', $pattern);
                })
                ->latest()
                ->limit(5)
                ->get();

            $clients = Client::query()
                ->where(function ($query) use ($pattern) {
                    $query->where('name', 'like', $pattern)
                        ->orWhere('email', 'like', $pattern)
                        ->orWhere('phone', 'like', $pattern);
                })
                ->latest()
                ->limit(5)
                ->get();

            $suppliers = Supplier::query()
                ->where(function ($query) use ($pattern) {
                    $query->where('name', 'like', $pattern)
                        ->orWhere('email', 'like', $pattern)
                        ->orWhere('phone', 'like', $pattern);
                })
                ->latest()
                ->limit(5)
                ->get();

            $sales = Sale::query()
                ->with('client')
                ->where(function ($query) use ($pattern) {
                    $query->where('sale_number', 'like', $pattern)
                        ->orWhereHas('client', function ($clientQuery) use ($pattern) {
                            $clientQuery->where('name', 'like', $pattern);
                        });
                })
                ->latest('sale_date')
                ->limit(5)
                ->get();

            $purchases = Purchase::query()
                ->with('supplier')
                ->where(function ($query) use ($pattern) {
                    $query->where('purchase_number', 'like', $pattern)
                        ->orWhereHas('supplier', function ($supplierQuery) use ($pattern) {
                            $supplierQuery->where('name', 'like', $pattern);
                        });
                })
                ->latest('purchase_date')
                ->limit(5)
                ->get();

            $invoices = Invoice::query()
                ->where(function ($query) use ($pattern) {
                    $query->where('invoice_number', 'like', $pattern)
                        ->orWhere('client_name', 'like', $pattern);
                })
                ->latest('issued_at')
                ->limit(5)
                ->get();

            $expenses = Expense::query()
                ->where(function ($query) use ($pattern) {
                    $query->where('category', 'like', $pattern)
                        ->orWhere('description', 'like', $pattern);
                })
                ->latest('expense_date')
                ->limit(5)
                ->get();
        }

        return view(
            'search.index',
            compact(
                'term',
                'products',
                'clients',
                'suppliers',
                'sales',
                'purchases',
                'invoices',
                'expenses'
            )
        );
    }
}
