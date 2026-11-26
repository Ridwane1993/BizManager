<?php

namespace Tests\Feature;

use App\Mail\InvoiceMail;
use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdvancedInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_stores_due_date_and_rejects_a_past_one(): void
    {
        [$admin, $sale] = $this->createSale();

        $this->actingAs($admin)
            ->get(route('sales.invoices.create', $sale))
            ->assertOk()
            ->assertSee('Date d’échéance')
            ->assertSee(now()->addDays(30)->toDateString());

        $dueDate = now()->addDays(15)->toDateString();

        $this->post(route('sales.invoices.store', $sale), [
            'tax_rate' => '0',
            'due_date' => $dueDate,
        ])->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->getKey(),
            'due_date' => $dueDate,
        ]);

        $this->post(route('sales.invoices.store', $this->createSale()[1]), [
            'tax_rate' => '0',
            'due_date' => now()->subDay()->toDateString(),
        ])->assertSessionHasErrors('due_date');
    }

    public function test_partial_payments_update_status_and_remaining_amount(): void
    {
        [$admin, $sale] = $this->createSale();
        $this->actingAs($admin);

        $this->post(route('sales.invoices.store', $sale), [
            'tax_rate' => '20',
        ])->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $this->assertSame(120.0, (float) $invoice->total_amount);

        $this->post(route('invoices.payments.store', $invoice), [
            'amount' => '50',
            'paid_at' => now()->toDateString(),
            'note' => 'Acompte',
        ])->assertRedirect(route('invoices.show', $invoice));

        $this->assertDatabaseHas('invoice_payments', [
            'invoice_id' => $invoice->getKey(),
            'amount' => '50.00',
            'note' => 'Acompte',
        ]);

        $this->assertSame('partial', $invoice->fresh()->payment_status);
        $this->assertSame(50.0, $invoice->paidAmount());
        $this->assertSame(70.0, $invoice->remainingAmount());

        $this->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Partiellement payée')
            ->assertSee('Acompte');

        $this->post(route('invoices.payments.store', $invoice), [
            'amount' => '70',
            'paid_at' => now()->toDateString(),
        ])->assertRedirect(route('invoices.show', $invoice));

        $this->assertSame('paid', $invoice->fresh()->payment_status);
        $this->assertNotNull($invoice->fresh()->paid_at);
        $this->assertSame(0.0, $invoice->remainingAmount());

        $this->from(route('invoices.show', $invoice))
            ->post(route('invoices.payments.store', $invoice), [
                'amount' => '10',
                'paid_at' => now()->toDateString(),
            ])
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHasErrors('amount');
    }

    public function test_invoice_pdf_is_generated_server_side(): void
    {
        [$admin, $sale] = $this->createSale();
        $this->actingAs($admin);

        $this->post(route('sales.invoices.store', $sale), [
            'tax_rate' => '20',
            'due_date' => now()->addDays(10)->toDateString(),
        ])->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $response = $this->get(route('invoices.pdf', $invoice));

        $response->assertOk();
        $this->assertSame(
            'application/pdf',
            $response->headers->get('Content-Type')
        );
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_invoice_can_be_emailed_with_its_pdf_attached(): void
    {
        Mail::fake();

        [$admin, $sale] = $this->createSale();
        $this->actingAs($admin);

        $this->post(route('sales.invoices.store', $sale), [
            'tax_rate' => '0',
        ])->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $this->post(route('invoices.send', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('success');

        Mail::assertSent(InvoiceMail::class, function (InvoiceMail $mail) use ($invoice): bool {
            return $mail->hasTo('customer@example.com')
                && str_contains($mail->envelope()->subject, $invoice->invoice_number)
                && count($mail->attachments()) === 1;
        });

        $this->assertNotNull($invoice->fresh()->sent_at);
    }

    public function test_invoice_without_client_email_cannot_be_sent(): void
    {
        Mail::fake();

        [$admin, $sale] = $this->createSale();
        $sale->client->update(['email' => null]);
        $this->actingAs($admin);

        $this->post(route('sales.invoices.store', $sale), [
            'tax_rate' => '0',
        ])->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $this->post(route('invoices.send', $invoice))
            ->assertRedirect(route('invoices.show', $invoice))
            ->assertSessionHas('error');

        Mail::assertNothingSent();
        $this->assertNull($invoice->fresh()->sent_at);
    }

    public function test_employee_cannot_register_a_payment_or_send_an_invoice(): void
    {
        [$admin, $sale] = $this->createSale();
        $company = $admin->company;
        $employee = User::factory()->create([
            'role' => 'employee',
            'company_id' => $company->getKey(),
        ]);

        $this->actingAs($admin)
            ->post(route('sales.invoices.store', $sale), [
                'tax_rate' => '0',
            ])->assertRedirect();

        $invoice = Invoice::firstOrFail();

        $this->actingAs($employee)
            ->post(route('invoices.payments.store', $invoice), [
                'amount' => '10',
                'paid_at' => now()->toDateString(),
            ])
            ->assertForbidden();

        $this->post(route('invoices.send', $invoice))->assertForbidden();
        $this->get(route('invoices.pdf', $invoice))->assertOk();
    }

    private function createSale(): array
    {
        $company = Company::create([
            'name' => 'Advanced invoice company',
            'slug' => 'advanced-invoice-'.Str::uuid(),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->company()->associate($company);
        $admin->save();

        $this->actingAs($admin);

        $client = Client::create([
            'name' => 'Invoice customer',
            'email' => 'customer@example.com',
        ]);

        $category = Category::create([
            'name' => 'Advanced invoice category '.Str::uuid(),
        ]);
        $product = Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Advanced invoice product',
            'reference' => 'AI-'.Str::upper(Str::random(6)),
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
}
