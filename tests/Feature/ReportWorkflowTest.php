<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_totals_respect_dates_statuses_and_company_scope(): void
    {
        $this->travelTo(Carbon::parse('2026-01-15 12:00:00'));

        $companyA = $this->createCompany('Report company A');
        $adminA = $this->createAdmin($companyA, 'report-a@example.test');
        $this->actingAs($adminA);

        $this->createSale(100, '2026-01-02', 'completed');
        $this->createSale(50, '2026-01-10', 'completed');
        $this->createSale(700, '2026-01-04', 'pending');
        $this->createSale(40, '2025-12-31', 'completed');
        $this->createPurchase(75, '2026-01-05', 'received');
        $this->createPurchase(25, '2026-01-10', 'received');
        $this->createPurchase(55, '2026-01-06', 'pending');
        $this->createPurchase(80, '2025-12-30', 'received');
        $this->createExpense(25, '2026-01-03');
        $this->createExpense(10, '2026-01-10');
        $this->createExpense(40, '2025-12-30');

        $companyB = $this->createCompany('Report company B');
        $adminB = $this->createAdmin($companyB, 'report-b@example.test');
        $this->actingAs($adminB);
        $this->createSale(900, '2026-01-02', 'completed');
        $this->createPurchase(900, '2026-01-05', 'received');
        $this->createExpense(900, '2026-01-03');

        $this->actingAs($adminA)
            ->get(route('reports.index', [
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-10',
            ]))
            ->assertOk()
            ->assertSeeText('Période du 01/01/2026')
            ->assertSeeText('au 10/01/2026')
            ->assertSeeText('Ventes terminées')
            ->assertSeeText('Achats reçus')
            ->assertSeeText('Dépenses enregistrées')
            ->assertSeeText('Exporter CSV')
            ->assertViewHas('salesSummary', static fn ($summary): bool => (int) $summary->record_count === 2
                && (float) $summary->total_amount === 150.0
            )
            ->assertViewHas('purchaseSummary', static fn ($summary): bool => (int) $summary->record_count === 2
                && (float) $summary->total_amount === 100.0
            )
            ->assertViewHas('expenseSummary', static fn ($summary): bool => (int) $summary->record_count === 2
                && (float) $summary->total_amount === 35.0
            )
            ->assertDontSeeText('900');

        $export = $this->get(route('reports.export', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-10',
        ]))
            ->assertOk()
            ->assertDownload('rapport-activite-2026-01-01-2026-01-10.csv');

        $csv = $export->streamedContent();
        $rows = array_map(
            static fn (string $line): array => str_getcsv(rtrim($line, "\r"), ';'),
            explode("\n", trim(substr($csv, 3)))
        );

        $this->assertContains(['Ventes terminées', '2', '150.00'], $rows);
        $this->assertContains(['Achats reçus', '2', '100.00'], $rows);
        $this->assertContains(['Dépenses enregistrées', '2', '35.00'], $rows);
        $this->assertStringNotContainsString('900', $csv);
    }

    public function test_report_defaults_to_month_to_date_and_rejects_invalid_date_ranges(): void
    {
        $this->travelTo(Carbon::parse('2026-01-15 12:00:00'));
        $company = $this->createCompany('Report validation company');
        $admin = $this->createAdmin($company, 'report-validation@example.test');

        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSeeText('Période du 01/01/2026')
            ->assertSeeText('au 15/01/2026');

        $this->from(route('reports.index'))
            ->get(route('reports.index', [
                'start_date' => '2026-01-10',
                'end_date' => '2026-01-01',
            ]))
            ->assertRedirect(route('reports.index'))
            ->assertSessionHasErrors(['start_date', 'end_date']);

        $this->from(route('reports.index'))
            ->get(route('reports.index', ['start_date' => '2026-01-01']))
            ->assertRedirect(route('reports.index'))
            ->assertSessionHasErrors('end_date');
    }

    public function test_report_requires_access_to_sales_purchases_and_expenses(): void
    {
        $company = $this->createCompany('Report role company');
        $employee = User::factory()->create(['role' => 'employee']);
        $employee->company()->associate($company);
        $employee->save();

        $this->actingAs($employee)
            ->get(route('reports.index'))
            ->assertForbidden();

        $this->get(route('reports.export'))
            ->assertForbidden();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('reports.index'));
    }

    private function createSale(float $total, string $date, string $status): Sale
    {
        $client = Client::create(['name' => 'Report customer '.Str::random(8)]);

        return Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => 'REP-SALE-'.Str::upper(Str::random(10)),
            'sale_date' => $date,
            'total_amount' => $total,
            'status' => $status,
        ]);
    }

    private function createPurchase(
        float $total,
        string $date,
        string $status
    ): Purchase {
        $supplier = Supplier::create([
            'name' => 'Report supplier '.Str::random(8),
        ]);

        return Purchase::create([
            'supplier_id' => $supplier->getKey(),
            'purchase_number' => 'REP-PUR-'.Str::upper(Str::random(10)),
            'purchase_date' => $date,
            'total_amount' => $total,
            'status' => $status,
        ]);
    }

    private function createExpense(float $amount, string $date): Expense
    {
        return Expense::create([
            'category' => 'Report expense',
            'amount' => $amount,
            'expense_date' => $date,
        ]);
    }

    private function createCompany(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::uuid(),
        ]);
    }

    private function createAdmin(Company $company, string $email): User
    {
        $admin = User::factory()->create([
            'email' => $email,
            'role' => 'admin',
        ]);
        $admin->company()->associate($company);
        $admin->save();

        return $admin;
    }
}
