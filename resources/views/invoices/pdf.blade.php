<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
        }

        h1 { font-size: 20px; margin: 0 0 4px; }
        h2 { font-size: 15px; margin: 0 0 12px; }
        h3 { font-size: 11px; text-transform: uppercase; color: #6b7280; margin: 0 0 6px; }

        .header { border-bottom: 2px solid #0d6efd; padding-bottom: 16px; margin-bottom: 24px; }
        .muted { color: #6b7280; }
        .right { text-align: right; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; }
        thead th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        tfoot th { border-bottom: none; }

        .section { margin-bottom: 24px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; background: #fef3c7; }
        .badge.paid { background: #d1fae5; }
        .badge.partial { background: #dbeafe; }
        .notes { margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>{{ $invoice->company->name }}</h1>
                    @if ($invoice->company->address)
                        <div class="muted">{!! nl2br(e($invoice->company->address)) !!}</div>
                    @endif
                    @if ($invoice->company->email)
                        <div class="muted">{{ $invoice->company->email }}</div>
                    @endif
                    @if ($invoice->company->phone)
                        <div class="muted">{{ $invoice->company->phone }}</div>
                    @endif
                </td>
                <td class="right">
                    <h2>Facture {{ $invoice->invoice_number }}</h2>
                    <div>Émise le {{ $invoice->issued_at->format('d/m/Y') }}</div>
                    @if ($invoice->due_date)
                        <div>Échéance : {{ $invoice->due_date->format('d/m/Y') }}</div>
                    @endif
                    <div class="badge {{ $invoice->payment_status }}">
                        @if ($invoice->payment_status === 'paid')
                            Payée
                        @elseif ($invoice->payment_status === 'partial')
                            Partiellement payée
                        @else
                            Non payée
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Facturé à</h3>
        <div><strong>{{ $invoice->client_name }}</strong></div>
        @if ($invoice->client_address)
            <div>{!! nl2br(e($invoice->client_address)) !!}</div>
        @endif
        @if ($invoice->client_email)
            <div>{{ $invoice->client_email }}</div>
        @endif
        @if ($invoice->client_phone)
            <div>{{ $invoice->client_phone }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Référence</th>
                <th class="right">Qté</th>
                <th class="right">Prix unitaire</th>
                <th class="right">Sous-total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->product_reference ?: '—' }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2, ',', ' ') }} FCFA</td>
                    <td class="right">{{ number_format($item->subtotal, 2, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="right">Sous-total</th>
                <th class="right">{{ number_format($invoice->subtotal, 2, ',', ' ') }} FCFA</th>
            </tr>
            <tr>
                <th colspan="4" class="right">Taxe ({{ number_format($invoice->tax_rate, 2, ',', ' ') }} %)</th>
                <th class="right">{{ number_format($invoice->tax_amount, 2, ',', ' ') }} FCFA</th>
            </tr>
            <tr>
                <th colspan="4" class="right">Total à payer</th>
                <th class="right">{{ number_format($invoice->total_amount, 2, ',', ' ') }} FCFA</th>
            </tr>
        </tfoot>
    </table>

    @if ($invoice->payments->isNotEmpty())
        <div class="section" style="margin-top: 24px;">
            <h3>Paiements enregistrés</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Note</th>
                        <th class="right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at->format('d/m/Y') }}</td>
                            <td>{{ $payment->note ?: '—' }}</td>
                            <td class="right">{{ number_format($payment->amount, 2, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="right"><strong>Reste dû :</strong> {{ number_format($invoice->remainingAmount(), 2, ',', ' ') }} FCFA</p>
        </div>
    @endif

    @if ($invoice->notes)
        <div class="notes">
            <h3>Notes</h3>
            <div>{!! nl2br(e($invoice->notes)) !!}</div>
        </div>
    @endif
</body>
</html>
