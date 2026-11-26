<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_creation_page_shows_sale_details_and_total_preview(): void
    {
        [$admin, $sale] = $this->createSale();

        $this->actingAs($admin)
            ->get(route('sales.invoices.create', $sale))
            ->assertOk()
            ->assertSee($sale->sale_number)
            ->assertSee('Invoice customer')
            ->assertSee('Original product name')
            ->assertSee('data-invoice-summary', false)
            ->assertSee('data-invoice-tax-amount', false)
            ->assertSee('data-subtotal="100.00"', false)
            ->assertSee('col-12 col-xl-8', false)
            ->assertSee('table-responsive', false)
            ->assertDontSee("taxRateInput.addEventListener('input', updateTotals)", false)
            ->assertSee('Émettre la facture');

        $this->assertFileExists(resource_path('js/modules/invoice-form.js'));
    }

    public function test_invoice_snapshots_sale_data_calculates_tax_and_tracks_payment(): void
    {
        [$admin, $sale, $product] = $this->createSale();

        $response = $this->actingAs($admin)
            ->post(route('sales.invoices.store', $sale), [
                'tax_rate' => '20.00',
            ]);

        $invoice = Invoice::firstOrFail();

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->actingAs($admin)
            ->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number)
            ->assertSee('Original product name');
        $this->get(route('invoices.index'))
            ->assertOk()
            ->assertSee($invoice->invoice_number);

        $this->assertDatabaseHas('invoices', [
            'sale_id' => $sale->getKey(),
            'invoice_number' => 'FAC-'.$sale->sale_number,
            'subtotal' => '100.00',
            'tax_rate' => '20.00',
            'tax_amount' => '20.00',
            'total_amount' => '120.00',
            'payment_status' => 'unpaid',
            'company_id' => $admin->company_id,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->getKey(),
            'product_name' => 'Original product name',
            'quantity' => 2,
            'subtotal' => '100.00',
        ]);

        $product->update(['name' => 'Changed product name']);

        $this->actingAs($admin)
            ->patch(route('invoices.payment-status', $invoice), [
                'payment_status' => 'paid',
            ])
            ->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->getKey(),
            'product_name' => 'Original product name',
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->getKey(),
            'payment_status' => 'paid',
        ]);
    }

    public function test_invoice_rejects_invalid_tax_and_prevents_deleting_invoiced_sale(): void
    {
        [$admin, $sale] = $this->createSale();

        $this->actingAs($admin)
            ->post(route('sales.invoices.store', $sale), [
                'tax_rate' => '101',
            ])
            ->assertSessionHasErrors('tax_rate');

        $this->assertDatabaseMissing('invoices', [
            'sale_id' => $sale->getKey(),
        ]);

        $this->post(route('sales.invoices.store', $sale), [
            'tax_rate' => '0',
        ])->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $this->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.show', $sale))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('sales', [
            'id' => $sale->getKey(),
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->getKey(),
        ]);
    }

    public function test_invoice_index_filters_by_number_client_and_payment_status_with_company_scope(): void
    {
        $companyA = Company::create([
            'name' => 'Invoice filter company A',
            'slug' => 'invoice-filter-a-'.Str::uuid(),
        ]);
        $adminA = $this->createAdmin($companyA);
        $this->actingAs($adminA);
        $targetInvoice = $this->createInvoiceRecord(
            'FAC-ALPHA-001',
            'Alpha Client',
            'unpaid'
        );
        $paidInvoice = $this->createInvoiceRecord(
            'FAC-BETA-002',
            'Beta Client',
            'paid'
        );

        $companyB = Company::create([
            'name' => 'Invoice filter company B',
            'slug' => 'invoice-filter-b-'.Str::uuid(),
        ]);
        $adminB = $this->createAdmin($companyB);
        $this->actingAs($adminB);
        $foreignInvoice = $this->createInvoiceRecord(
            'FAC-ALPHA-003',
            'Foreign Alpha Client',
            'unpaid'
        );

        $this->actingAs($adminA)
            ->get(route('invoices.index', [
                'search' => 'ALPHA',
                'payment_status' => 'unpaid',
            ]))
            ->assertOk()
            ->assertSee($targetInvoice->invoice_number)
            ->assertSee($targetInvoice->client_name)
            ->assertDontSee($paidInvoice->invoice_number)
            ->assertDontSee($foreignInvoice->invoice_number)
            ->assertSee('Effacer');

        $this->get(route('invoices.index', [
            'search' => $paidInvoice->client_name,
            'payment_status' => 'paid',
        ]))
            ->assertOk()
            ->assertSee($paidInvoice->invoice_number)
            ->assertDontSee($targetInvoice->invoice_number);
    }

    public function test_invoice_index_validates_search_and_payment_status_filters(): void
    {
        [$admin] = $this->createSale();
        $this->actingAs($admin);

        $this->from(route('invoices.index'))
            ->get(route('invoices.index', ['search' => str_repeat('x', 101)]))
            ->assertRedirect(route('invoices.index'))
            ->assertSessionHasErrors('search');

        $this->from(route('invoices.index'))
            ->get(route('invoices.index', ['payment_status' => 'pending']))
            ->assertRedirect(route('invoices.index'))
            ->assertSessionHasErrors('payment_status');
    }

    private function createSale(): array
    {
        $company = Company::create([
            'name' => 'Invoice company',
            'slug' => 'invoice-company-'.Str::uuid(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $admin->company()->associate($company);
        $admin->save();

        $this->actingAs($admin);

        $client = Client::create([
            'name' => 'Invoice customer',
            'email' => 'customer@example.com',
        ]);

        $category = Category::create(['name' => 'Invoice category']);
        $product = Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Original product name',
            'reference' => 'INVOICE-001',
            'purchase_price' => 25,
            'selling_price' => 50,
            'stock' => 4,
            'stock_alert_threshold' => 1,
        ]);

        $sale = Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => 'V-'.Str::upper(Str::random(10)),
            'sale_date' => now()->toDateString(),
            'total_amount' => 100,
            'status' => 'completed',
        ]);

        $sale->items()->create([
            'product_id' => $product->getKey(),
            'quantity' => 2,
            'unit_price' => 50,
            'subtotal' => 100,
        ]);

        return [$admin, $sale, $product];
    }

    private function createInvoiceRecord(
        string $invoiceNumber,
        string $clientName,
        string $paymentStatus
    ): Invoice {
        $client = Client::create(['name' => $clientName]);
        $sale = Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => 'INV-'.Str::upper(Str::random(10)),
            'sale_date' => now()->toDateString(),
            'total_amount' => 150,
            'status' => 'completed',
        ]);

        return Invoice::create([
            'sale_id' => $sale->getKey(),
            'invoice_number' => $invoiceNumber,
            'issued_at' => now()->toDateString(),
            'client_name' => $clientName,
            'subtotal' => 150,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total_amount' => 150,
            'payment_status' => $paymentStatus,
        ]);
    }

    private function createAdmin(Company $company): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->company()->associate($company);
        $admin->save();

        return $admin;
    }
}
