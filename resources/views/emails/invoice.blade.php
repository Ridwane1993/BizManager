<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <p>Bonjour {{ $invoice->client_name }},</p>

    <p>
        Vous trouverez ci-joint la facture
        <strong>{{ $invoice->invoice_number }}</strong>
        émise le {{ $invoice->issued_at->format('d/m/Y') }}
        @if ($invoice->due_date)
            , avec une échéance au {{ $invoice->due_date->format('d/m/Y') }}
        @endif
        .
    </p>

    <p>
        Montant total : <strong>{{ number_format($invoice->total_amount, 2, ',', ' ') }} FCFA</strong>.
    </p>

    <p>Merci de votre confiance.</p>

    <p>
        {{ $invoice->company->name }}<br>
        @if ($invoice->company->email)
            {{ $invoice->company->email }}<br>
        @endif
        @if ($invoice->company->phone)
            {{ $invoice->company->phone }}
        @endif
    </p>
</body>
</html>
