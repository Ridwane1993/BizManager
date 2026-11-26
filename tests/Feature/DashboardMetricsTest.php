<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_only_the_current_company_unpaid_invoice_total(): void
    {
        $companyA = $this->createCompany('Dashboard A');
        $adminA = $this->createAdmin($companyA, 'dashboard-a@example.test');

        $this->actingAs($adminA);
        $this->createInvoice(100, 'unpaid');
        $this->createInvoice(50, 'unpaid');
        $this->createInvoice(30, 'paid');

        $companyB = $this->createCompany('Dashboard B');
        $adminB = $this->createAdmin($companyB, 'dashboard-b@example.test');
        $this->actingAs($adminB);
        $this->createInvoice(900, 'unpaid');

        $this->actingAs($adminA)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('À encaisser')
            ->assertSee('150')
            ->assertSee('factures')
            ->assertSee('impayées')
            ->assertSee(route('invoices.index'))
            ->assertSee('col-12 col-sm-6 col-xl', false)
            ->assertDontSee('900');
    }

    public function test_dashboard_does_not_report_new_activity_when_monthly_metrics_are_zero(): void
    {
        $company = $this->createCompany('Empty dashboard');
        $admin = $this->createAdmin($company, 'empty-dashboard@example.test');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aucune vente ce mois-ci')
            ->assertSee('Aucun nouveau client ce mois-ci')
            ->assertSee('Aucun nouveau produit ce mois-ci')
            ->assertSee('Aucune dépense ce mois-ci')
            ->assertSee('Aucune dépense enregistrée ce mois-ci.')
            ->assertSee('Aucune vente de produits ce mois-ci.')
            ->assertSee('neutral', false);
    }

    public function test_dashboard_expense_total_is_current_month_and_company_scoped(): void
    {
        $companyA = $this->createCompany('Expense dashboard A');
        $adminA = $this->createAdmin($companyA, 'expense-dashboard-a@example.test');
        $today = now();
        $previousMonth = $today->copy()->startOfMonth()->subMonth();

        $this->actingAs($adminA);
        $this->createExpense('Utilities', 75, $today->toDateString());
        $this->createExpense('Transport', 50, $today->toDateString());
        $this->createExpense('Previous month', 40, $previousMonth->toDateString());

        $companyB = $this->createCompany('Expense dashboard B');
        $adminB = $this->createAdmin($companyB, 'expense-dashboard-b@example.test');
        $this->actingAs($adminB);
        $this->createExpense('Other company', 900, $today->toDateString());

        $this->actingAs($adminA)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dépenses du mois')
            ->assertSee('125')
            ->assertSee('FCFA')
            ->assertSee('212,5%')
            ->assertSee('Utilities')
            ->assertSee('"total":75', false)
            ->assertSee('Transport')
            ->assertSee('"total":50', false)
            ->assertDontSee('Previous month')
            ->assertDontSee('900');
    }

    public function test_dashboard_lists_current_month_top_products_for_the_current_company(): void
    {
        $this->travelTo(Carbon::parse('2026-01-15 12:00:00'));

        $companyA = $this->createCompany('Top products A');
        $adminA = $this->createAdmin($companyA, 'top-products-a@example.test');
        $this->actingAs($adminA);

        $bestSeller = $this->createProductForDashboard(
            'Best seller',
            'TOP-001'
        );
        $secondSeller = $this->createProductForDashboard(
            'Second seller',
            'TOP-002'
        );
        $pastProduct = $this->createProductForDashboard(
            'Past product',
            'TOP-003'
        );
        $pendingProduct = $this->createProductForDashboard(
            'Pending product',
            'TOP-004'
        );

        $this->createProductSale($bestSeller, 3, 15, '2026-01-05');
        $this->createProductSale($bestSeller, 2, 15, '2026-01-10');
        $this->createProductSale($secondSeller, 2, 10, '2026-01-12');
        $this->createProductSale($pastProduct, 99, 10, '2025-12-31');
        $this->createProductSale(
            $pendingProduct,
            90,
            10,
            '2026-01-14',
            'pending'
        );

        $companyB = $this->createCompany('Top products B');
        $adminB = $this->createAdmin($companyB, 'top-products-b@example.test');
        $this->actingAs($adminB);
        $foreignProduct = $this->createProductForDashboard(
            'Foreign product',
            'TOP-005'
        );
        $this->createProductSale($foreignProduct, 500, 10, '2026-01-14');

        $this->actingAs($adminA)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Produits les plus vendus')
            ->assertViewHas('topProducts', static function ($products): bool {
                return $products->map(static fn (array $product): array => [
                    $product['name'],
                    $product['quantity_sold'],
                    $product['total_revenue'],
                ])->all() === [
                    ['Best seller', 5, 75.0],
                    ['Second seller', 2, 20.0],
                ];
            })
            ->assertDontSee('Past product')
            ->assertDontSee('Pending product')
            ->assertDontSee('Foreign product');
    }

    public function test_sales_chart_route_returns_company_scoped_daily_totals(): void
    {
        $companyA = $this->createCompany('Sales chart A');
        $adminA = $this->createAdmin($companyA, 'sales-chart-a@example.test');

        $this->actingAs($adminA);
        $this->createInvoice(125, 'unpaid');

        $companyB = $this->createCompany('Sales chart B');
        $adminB = $this->createAdmin($companyB, 'sales-chart-b@example.test');
        $this->actingAs($adminB);
        $this->createInvoice(900, 'unpaid');

        $response = $this->actingAs($adminA)
            ->getJson(route('dashboard.sales-chart', ['period' => 'week']))
            ->assertOk()
            ->assertJsonPath('period', 'week');

        $data = $response->json('data');
        $this->assertCount(7, $data);

        $today = collect($data)->firstWhere('date', now()->toDateString());
        $this->assertNotNull($today);
        $this->assertEquals(125, $today['total']);
        $this->assertEquals(125, array_sum(array_map(
            static fn (array $day): float => (float) $day['total'],
            $data
        )));
    }

    public function test_sales_chart_aggregates_months_across_year_boundaries_and_validates_periods(): void
    {
        $this->travelTo(Carbon::parse('2026-01-15 12:00:00'));

        $companyA = $this->createCompany('Sales aggregation A');
        $adminA = $this->createAdmin($companyA, 'sales-aggregation-a@example.test');
        $this->actingAs($adminA);
        $this->createSaleForChart(10, '2025-11-02');
        $this->createSaleForChart(20, '2025-12-15');
        $this->createSaleForChart(30, '2026-01-05');
        $this->createSaleForChart(40, '2025-10-31');

        $companyB = $this->createCompany('Sales aggregation B');
        $adminB = $this->createAdmin($companyB, 'sales-aggregation-b@example.test');
        $this->actingAs($adminB);
        $this->createSaleForChart(900, '2025-12-03');

        $salesQueryCount = 0;
        DB::listen(static function (QueryExecuted $query) use (&$salesQueryCount): void {
            if (str_contains($query->sql, 'sales')) {
                $salesQueryCount++;
            }
        });

        $monthData = $this->actingAs($adminA)
            ->getJson(route('dashboard.sales-chart', ['period' => 'month']))
            ->assertOk()
            ->json('data');

        $this->assertSame(1, $salesQueryCount);
        $this->assertCount(31, $monthData);
        $this->assertEquals(
            30,
            collect($monthData)->firstWhere('date', '2026-01-05')['total']
        );

        $salesQueryCount = 0;
        $quarterData = $this->getJson(route(
            'dashboard.sales-chart',
            ['period' => 'quarter']
        ))
            ->assertOk()
            ->json('data');

        $this->assertSame(1, $salesQueryCount);
        $this->assertSame(
            ['2025-11', '2025-12', '2026-01'],
            array_column($quarterData, 'date')
        );
        $this->assertSame(
            [10.0, 20.0, 30.0],
            array_map(
                static fn (array $month): float => (float) $month['total'],
                $quarterData
            )
        );

        $this->getJson(route('dashboard.sales-chart', ['period' => 'year']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('period');

        $this->get(route('dashboard', ['period' => 'year']))
            ->assertRedirect()
            ->assertSessionHasErrors('period');
    }

    private function createExpense(string $category, float $amount, string $date): Expense
    {
        return Expense::create([
            'category' => $category,
            'amount' => $amount,
            'expense_date' => $date,
        ]);
    }

    private function createSaleForChart(float $total, string $date): Sale
    {
        $client = Client::create(['name' => 'Chart client '.Str::random(8)]);

        return Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => 'CHART-'.Str::upper(Str::random(10)),
            'sale_date' => $date,
            'total_amount' => $total,
            'status' => 'completed',
        ]);
    }

    private function createProductForDashboard(
        string $name,
        string $reference
    ): Product {
        $category = Category::create(['name' => $name.' category']);

        return Product::create([
            'category_id' => $category->getKey(),
            'name' => $name,
            'reference' => $reference,
            'purchase_price' => 5,
            'selling_price' => 20,
            'stock' => 100,
            'stock_alert_threshold' => 1,
        ]);
    }

    private function createProductSale(
        Product $product,
        int $quantity,
        float $unitPrice,
        string $date,
        string $status = 'completed'
    ): void {
        $client = Client::create(['name' => 'Top product customer']);
        $sale = Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => 'TOP-'.Str::upper(Str::random(10)),
            'sale_date' => $date,
            'total_amount' => $quantity * $unitPrice,
            'status' => $status,
        ]);
        $sale->items()->create([
            'product_id' => $product->getKey(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ]);
    }

    private function createInvoice(float $total, string $paymentStatus): Invoice
    {
        $client = Client::create(['name' => 'Dashboard client']);
        $saleNumber = 'DASH-'.Str::upper(Str::random(10));
        $sale = Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => $saleNumber,
            'sale_date' => now()->toDateString(),
            'total_amount' => $total,
            'status' => 'completed',
        ]);

        return Invoice::create([
            'sale_id' => $sale->getKey(),
            'invoice_number' => 'FAC-'.$saleNumber,
            'issued_at' => now()->toDateString(),
            'client_name' => $client->name,
            'subtotal' => $total,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_amount' => $total,
            'payment_status' => $paymentStatus,
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
