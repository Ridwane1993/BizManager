<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_draft_does_not_touch_stock_until_it_is_completed_and_can_be_cancelled(): void
    {
        [$admin] = $this->createUserWithCompany('admin');
        $this->actingAs($admin);

        $client = Client::create(['name' => 'Workflow client']);
        $product = $this->createProduct('WF-SALE-001', 10);

        $this->post(route('sales.store'), [
            'client_id' => $client->getKey(),
            'sale_date' => now()->toDateString(),
            'status' => 'pending',
            'products' => [
                ['product_id' => $product->getKey(), 'quantity' => 4],
            ],
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::firstOrFail();

        $this->assertSame('pending', $sale->status);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(0, StockMovement::where('source_type', Sale::class)
            ->where('source_id', $sale->getKey())
            ->count());

        $this->get(route('sales.show', $sale))
            ->assertOk()
            ->assertSee('Valider la vente');

        $this->post(route('sales.complete', $sale))
            ->assertRedirect(route('sales.show', $sale));

        $this->assertSame('completed', $sale->fresh()->status);
        $this->assertSame(6, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'source_type' => Sale::class,
            'source_id' => $sale->getKey(),
            'type' => 'out',
            'quantity' => 4,
        ]);

        $this->post(route('sales.cancel', $sale))
            ->assertRedirect(route('sales.show', $sale));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'source_type' => Sale::class,
            'source_id' => $sale->getKey(),
            'type' => 'return',
            'quantity' => 4,
        ]);

        $this->post(route('sales.complete', $sale))
            ->assertRedirect(route('sales.show', $sale))
            ->assertSessionHas('error');
    }

    public function test_sale_completion_is_rejected_when_stock_became_insufficient(): void
    {
        [$admin] = $this->createUserWithCompany('admin');
        $this->actingAs($admin);

        $client = Client::create(['name' => 'Workflow client']);
        $product = $this->createProduct('WF-SALE-002', 12);

        $this->post(route('sales.store'), [
            'client_id' => $client->getKey(),
            'sale_date' => now()->toDateString(),
            'status' => 'pending',
            'products' => [
                ['product_id' => $product->getKey(), 'quantity' => 10],
            ],
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::firstOrFail();
        $product->update(['stock' => 3]);

        $this->from(route('sales.show', $sale))
            ->post(route('sales.complete', $sale))
            ->assertRedirect(route('sales.show', $sale))
            ->assertSessionHasErrors('sale');

        $this->assertSame('pending', $sale->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_purchase_draft_does_not_touch_stock_until_received_and_can_be_cancelled(): void
    {
        [$admin] = $this->createUserWithCompany('admin');
        $this->actingAs($admin);

        $supplier = Supplier::create(['name' => 'Workflow supplier']);
        $product = $this->createProduct('WF-BUY-001', 2);

        $this->post(route('purchases.store'), [
            'supplier_id' => $supplier->getKey(),
            'purchase_date' => now()->toDateString(),
            'status' => 'pending',
            'products' => [
                ['product_id' => $product->getKey(), 'quantity' => 5, 'unit_price' => 8],
            ],
        ])->assertRedirect(route('purchases.index'));

        $purchase = Purchase::firstOrFail();

        $this->assertSame('pending', $purchase->status);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(0, StockMovement::where('source_type', Purchase::class)
            ->where('source_id', $purchase->getKey())
            ->count());

        $this->get(route('purchases.show', $purchase))
            ->assertOk()
            ->assertSee('Réceptionner');

        $this->post(route('purchases.receive', $purchase))
            ->assertRedirect(route('purchases.show', $purchase));

        $this->assertSame('received', $purchase->fresh()->status);
        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame('8.00', $product->fresh()->purchase_price);

        $this->post(route('purchases.cancel', $purchase))
            ->assertRedirect(route('purchases.show', $purchase));

        $this->assertSame('cancelled', $purchase->fresh()->status);
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'source_type' => Purchase::class,
            'source_id' => $purchase->getKey(),
            'type' => 'out',
            'quantity' => 5,
        ]);
    }

    public function test_employee_cannot_validate_a_sale_but_can_create_an_immediate_one(): void
    {
        [, $company] = $this->createUserWithCompany('admin');
        $employee = User::factory()->create([
            'role' => 'employee',
            'company_id' => $company->getKey(),
        ]);
        $this->actingAs($employee);

        $client = Client::create(['name' => 'Workflow client']);
        $product = $this->createProduct('WF-EMP-001', 5);

        $this->post(route('sales.store'), [
            'client_id' => $client->getKey(),
            'sale_date' => now()->toDateString(),
            'products' => [
                ['product_id' => $product->getKey(), 'quantity' => 1],
            ],
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::firstOrFail();

        $this->assertSame('completed', $sale->status);
        $this->post(route('sales.complete', $sale))->assertForbidden();
        $this->post(route('sales.cancel', $sale))->assertForbidden();
        $this->get(route('sales.create'))->assertDontSee('Brouillon / devis');
    }

    private function createUserWithCompany(string $role): array
    {
        $company = Company::create([
            'name' => 'Workflow company',
            'slug' => 'workflow-'.Str::uuid(),
        ]);
        $user = User::factory()->create([
            'role' => $role,
            'company_id' => $company->getKey(),
        ]);

        return [$user, $company];
    }

    private function createProduct(string $reference, int $stock): Product
    {
        $category = Category::create([
            'name' => 'Workflow category '.Str::uuid(),
        ]);

        return Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Workflow product',
            'reference' => $reference,
            'purchase_price' => 5,
            'selling_price' => 12,
            'stock' => $stock,
            'stock_alert_threshold' => 1,
        ]);
    }
}
