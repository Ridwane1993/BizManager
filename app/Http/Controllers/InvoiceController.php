<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'payment_status' => [
                'nullable',
                Rule::in(['unpaid', 'paid']),
            ],
        ]);

        $query = Invoice::query();
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%");
            });
        }

        if (isset($validated['payment_status'])) {
            $query->where('payment_status', $validated['payment_status']);
        }

        $invoices = $query
            ->latest('issued_at')
            ->paginate(15)
            ->withQueryString();

        $filters = [
            'search' => $search,
            'payment_status' => $validated['payment_status'] ?? '',
        ];

        return view('invoices.index', compact('invoices', 'filters'));
    }

    public function create(Sale $sale)
    {
        if ($sale->status !== 'completed') {
            abort(404);
        }

        if ($sale->invoice()->exists()) {
            return redirect()->route(
                'invoices.show',
                $sale->invoice()->firstOrFail()
            );
        }

        $sale->load(['client', 'items.product']);

        return view('invoices.create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'tax_rate' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:100',
            ],
        ]);

        $invoice = DB::transaction(function () use (
            $sale,
            $validated,
            $request
        ) {
            $sale = Sale::where('company_id', $request->user()->company_id)
                ->whereKey($sale->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages([
                    'sale' => 'Seules les ventes terminées peuvent être facturées.',
                ]);
            }

            if ($sale->invoice()->exists()) {
                throw ValidationException::withMessages([
                    'sale' => 'Cette vente possède déjà une facture.',
                ]);
            }

            $sale->load(['client', 'items.product']);

            if ($sale->client === null || $sale->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'sale' => 'La vente doit avoir un client et au moins un produit.',
                ]);
            }

            $invoiceNumber = 'FAC-'.$sale->sale_number;
            $taxRate = (float) $validated['tax_rate'];
            $subtotal = 0;
            $itemSnapshots = [];

            foreach ($sale->items as $item) {
                if ($item->product === null) {
                    throw ValidationException::withMessages([
                        'sale' => 'Un produit de cette vente n’est plus disponible.',
                    ]);
                }

                $lineSubtotal = round(
                    (float) $item->unit_price * (int) $item->quantity,
                    2
                );
                $subtotal += $lineSubtotal;

                $itemSnapshots[] = [
                    'product_name' => $item->product->name,
                    'product_reference' => $item->product->reference,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $taxAmount = round($subtotal * $taxRate / 100, 2);

            $invoice = $sale->invoice()->create([
                'invoice_number' => $invoiceNumber,
                'issued_at' => now()->toDateString(),
                'client_name' => $sale->client->name,
                'client_email' => $sale->client->email,
                'client_phone' => $sale->client->phone,
                'client_address' => $sale->client->address,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => round($subtotal + $taxAmount, 2),
                'payment_status' => 'unpaid',
                'notes' => $sale->notes,
            ]);

            foreach ($itemSnapshots as $itemSnapshot) {
                $invoice->items()->create($itemSnapshot);
            }

            return $invoice;
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Facture créée avec succès.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'company']);

        return view('invoices.show', compact('invoice'));
    }

    public function updatePaymentStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_status' => [
                'required',
                Rule::in(['unpaid', 'paid']),
            ],
        ]);

        $invoice->update([
            'payment_status' => $validated['payment_status'],
            'paid_at' => $validated['payment_status'] === 'paid'
                ? now()
                : null,
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Statut de paiement mis à jour.');
    }
}
