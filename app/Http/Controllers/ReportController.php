<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('reports.index', $this->reportData($request));
    }

    public function export(Request $request)
    {
        $report = $this->reportData($request);

        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                throw new \RuntimeException('Unable to open CSV output stream.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Rapport d’activité'], ';', '"', '');
            fputcsv($output, ['Date de début', $report['startDate']], ';', '"', '');
            fputcsv($output, ['Date de fin', $report['endDate']], ';', '"', '');
            fputcsv($output, [], ';', '"', '');
            fputcsv($output, ['Indicateur', 'Nombre', 'Montant (FCFA)'], ';', '"', '');
            fputcsv($output, [
                'Ventes terminées',
                $report['salesSummary']->record_count,
                number_format((float) $report['salesSummary']->total_amount, 2, '.', ''),
            ], ';', '"', '');
            fputcsv($output, [
                'Achats reçus',
                $report['purchaseSummary']->record_count,
                number_format((float) $report['purchaseSummary']->total_amount, 2, '.', ''),
            ], ';', '"', '');
            fputcsv($output, [
                'Dépenses enregistrées',
                $report['expenseSummary']->record_count,
                number_format((float) $report['expenseSummary']->total_amount, 2, '.', ''),
            ], ';', '"', '');

            fclose($output);
        }, sprintf(
            'rapport-activite-%s-%s.csv',
            $report['startDate'],
            $report['endDate']
        ), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function reportData(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:end_date',
                'before_or_equal:end_date',
            ],
            'end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:start_date',
                'after_or_equal:start_date',
            ],
        ]);

        $startDate = $validated['start_date']
            ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date']
            ?? Carbon::today()->toDateString();

        $salesSummary = Sale::query()
            ->where('status', 'completed')
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->selectRaw(
                'COUNT(*) AS record_count, '
                .'COALESCE(SUM(total_amount), 0) AS total_amount'
            )
            ->first();

        $purchaseSummary = Purchase::query()
            ->where('status', 'received')
            ->whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->selectRaw(
                'COUNT(*) AS record_count, '
                .'COALESCE(SUM(total_amount), 0) AS total_amount'
            )
            ->first();

        $expenseSummary = Expense::query()
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->selectRaw(
                'COUNT(*) AS record_count, '
                .'COALESCE(SUM(amount), 0) AS total_amount'
            )
            ->first();

        return compact(
            'startDate',
            'endDate',
            'salesSummary',
            'purchaseSummary',
            'expenseSummary'
        );
    }
}
