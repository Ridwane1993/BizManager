<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoicePaymentRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoicePaymentStatusRequest;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Sale;
use App\Services\InvoicePdfGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
                Rule::in(['unpaid', 'partial', 'paid']),
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

        $defaultDueDate = now()
            ->addDays((int) config('invoices.default_due_days', 30))
            ->toDateString();

        return view('invoices.create', compact('sale', 'defaultDueDate'));
    }

    public function store(StoreInvoiceRequest $request, Sale $sale)
    {
        $validated = $request->validated();

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
                'due_date' => $validated['due_date'] ?? now()
                    ->addDays((int) config('invoices.default_due_days', 30))
                    ->toDateString(),
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
        $invoice->load(['items', 'company', 'payments']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Génère le PDF de la facture côté serveur.
     */
    public function pdf(Invoice $invoice, InvoicePdfGenerator $generator)
    {
        $pdf = $generator->generate($invoice);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'
                .$generator->filename($invoice).'"',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /**
     * Envoie la facture (avec son PDF) au client par e-mail.
     */
    public function send(Invoice $invoice, InvoicePdfGenerator $generator)
    {
        if (! $invoice->client_email) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Aucune adresse e-mail n’est renseignée pour ce client.');
        }

        $pdf = $generator->generate($invoice);

        Mail::to($invoice->client_email)
            ->send(new InvoiceMail($invoice, $pdf));

        $invoice->update(['sent_at' => now()]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Facture envoyée par e-mail avec succès.');
    }

    /**
     * Enregistre un paiement partiel ou solde la facture.
     */
    public function storePayment(
        StoreInvoicePaymentRequest $request,
        Invoice $invoice
    ) {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $invoice): void {
            $invoice = Invoice::whereKey($invoice->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            InvoicePayment::create([
                'invoice_id' => $invoice->getKey(),
                'amount' => $validated['amount'],
                'paid_at' => Carbon::parse($validated['paid_at'])
                    ->startOfDay(),
                'note' => $validated['note'] ?? null,
            ]);

            $invoice->refreshPaymentStatus();
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function updatePaymentStatus(
        UpdateInvoicePaymentStatusRequest $request,
        Invoice $invoice
    ) {
        $validated = $request->validated();

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
