<?php

namespace App\Services;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfGenerator
{
    /**
     * Génère le PDF d'une facture côté serveur.
     */
    public function generate(Invoice $invoice): string
    {
        $invoice->loadMissing(['company', 'items', 'payments']);

        $html = view('invoices.pdf', compact('invoice'))->render();

        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $options->set('isPhpEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function filename(Invoice $invoice): string
    {
        return 'facture-'.$invoice->invoice_number.'.pdf';
    }
}
