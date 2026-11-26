<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_create_update_and_delete_keep_stock_and_relations_consistent(): void
    {
        [$admin, $company] = $this->createAdmin();
        $this->actingAs($admin);

        $client = Client::create(['name' => 'Inventory client']);
        $product = $this->createProduct('SALE-001', 10, 12.50);

        $this->get(route('sales.create'))->assertOk();
        $this->post(route('sales.store'), [
            'client_id' => $client->getKey(),
            'sale_date' => now()->toDateString(),
            'products' => [
                ['product_id' => $product->getKey(), 'quantity' => 4],
            ],
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::firstOrFail();
        $product->refresh();

        $this->assertSame(6, $product->stock);
        $this->assertSame((int) $company->getKey(), (int) $sale->company_id);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 4,
            'subtotal' => '50.00',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->getKey(),
            'source_type' => Sale::class,
            'source_id' => $sale->getKey(),
            'type' => 'out',
            'quantity' => 4,
        ]);

        $this->get(route('sales.show', $sale))->assertOk();
        $this->get(route('sales.edit', $sale))->assertOk();
        $this->put(route('sales.update', $sale), [
            'client_id' => $client->getKey(),
            'sale_date' => now()->toDateString(),
            'products' => [
                ['product_id' => $product->getKey(), 'quantity' => 6],
            ],
        ])->assertRedirect(route('sales.index'));

        $sale->refresh();
        $product->refresh();

        $this->assertSame(4, $product->stock);
        $this->assertSame('75.00', $sale->total_amount);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->getKey(),
            'quantity' => 6,
            'subtotal' => '75.00',
        ]);

        $this->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'));

        $product->refresh();
        $this->assertSame(10, $product->stock);
        $this->assertDatabaseMissing('sales', ['id' => $sale->getKey()]);
        $this->assertDatabaseMissing('sale_items', ['sale_id' => $sale->getKey()]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->getKey(),
            'source_type' => Sale::class,
            'source_id' => $sale->getKey(),
            'type' => 'return',
            'quantity' => 6,
        ]);
    }

    public function test_sale_with_insufficient_stock_rolls_back_without_persisting(): void
    {
        [$admin, $company] = $this->createAdmin();
        $this->actingAs($admin);

        $client = Client::create(['name' => 'Inventory client']);
        $product = $this->createProduct('SALE-002', 2, 12.50);

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'client_id' => $client->getKey(),
                'sale_date' => now()->toDateString(),
                'products' => [
                    ['product_id' => $product->getKey(), 'quantity' => 3],
                ],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('products');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_sale_index_filters_by_number_client_status_dates_and_company_scope(): void
    {
        [$adminA] = $this->createAdmin();
        $this->actingAs($adminA);
        $clientA = Client::create(['name' => 'Atlas Client']);
        $targetSale = $this->createSaleRecord(
            $clientA,
            'SALE-ALPHA-001',
            '2026-03-10',
            'completed'
        );
        $pendingSale = $this->createSaleRecord(
            $clientA,
            'SALE-PENDING-002',
            '2026-03-05',
            'pending'
        );
        $oldSale = $this->createSaleRecord(
            $clientA,
            'SALE-OLD-003',
            '2026-02-28',
            'completed'
        );

        [$adminB] = $this->createAdmin();
        $this->actingAs($adminB);
        $clientB = Client::create(['name' => 'Atlas Client']);
        $foreignSale = $this->createSaleRecord(
            $clientB,
            'SALE-FOREIGN-004',
            '2026-03-10',
            'completed'
        );

        $this->actingAs($adminA)
            ->get(route('sales.index', [
                'search' => 'Atlas',
                'status' => 'completed',
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-10',
            ]))
            ->assertOk()
            ->assertSeeText($targetSale->sale_number)
            ->assertDontSeeText($pendingSale->sale_number)
            ->assertDontSeeText($oldSale->sale_number)
            ->assertDontSeeText($foreignSale->sale_number)
            ->assertSeeText('Effacer');

        $this->get(route('sales.index', [
            'search' => $targetSale->sale_number,
        ]))
            ->assertOk()
            ->assertSeeText($targetSale->sale_number);
    }

    public function test_sale_index_validates_search_status_and_date_filters(): void
    {
        [$admin] = $this->createAdmin();
        $this->actingAs($admin);

        $this->from(route('sales.index'))
            ->get(route('sales.index', ['search' => str_repeat('x', 101)]))
            ->assertRedirect(route('sales.index'))
            ->assertSessionHasErrors('search');

        $this->from(route('sales.index'))
            ->get(route('sales.index', ['status' => 'received']))
            ->assertRedirect(route('sales.index'))
            ->assertSessionHasErrors('status');

        $this->from(route('sales.index'))
            ->get(route('sales.index', [
                'start_date' => '2026-03-11',
                'end_date' => '2026-03-01',
            ]))
            ->assertRedirect(route('sales.index'))
            ->assertSessionHasErrors(['start_date', 'end_date']);

        $this->from(route('sales.index'))
            ->get(route('sales.index', ['start_date' => '2026-03-01']))
            ->assertRedirect(route('sales.index'))
            ->assertSessionHasErrors('end_date');
    }

    public function test_sale_index_paginates_and_keeps_active_filters(): void
    {
        [$admin] = $this->createAdmin();
        $this->actingAs($admin);
        $client = Client::create(['name' => 'Pagination Client']);

        for ($index = 1; $index <= 16; $index++) {
            $this->createSaleRecord(
                $client,
                sprintf('SALE-PAGINATION-%02d', $index),
                now()->toDateString(),
                'completed'
            );
        }

        $this->get(route('sales.index', [
            'search' => 'Pagination Client',
            'status' => 'completed',
        ]))
            ->assertOk()
            ->assertSee('page=2')
            ->assertViewHas('sales', static fn ($sales): bool => $sales->total() === 16
                && $sales->count() === 15
            );

        $this->get(route('sales.index', [
            'search' => 'Pagination Client',
            'status' => 'completed',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('sales', static fn ($sales): bool => $sales->currentPage() === 2
                && $sales->total() === 16
                && $sales->count() === 1
            );
    }

    public function test_purchase_create_update_and_delete_keep_stock_and_relations_consistent(): void
    {
        [$admin, $company] = $this->createAdmin();
        $this->actingAs($admin);

        $supplier = Supplier::create(['name' => 'Inventory supplier']);
        $product = $this->createProduct('PURCHASE-001', 3, 12.50);

        $this->get(route('purchases.create'))->assertOk();
        $this->post(route('purchases.store'), [
            'supplier_id' => $supplier->getKey(),
            'purchase_date' => now()->toDateString(),
            'products' => [
                [
                    'product_id' => $product->getKey(),
                    'quantity' => 4,
                    'unit_price' => '7.25',
                ],
            ],
        ])->assertRedirect(route('purchases.index'));

        $purchase = Purchase::firstOrFail();
        $product->refresh();

        $this->assertSame(7, $product->stock);
        $this->assertSame((int) $company->getKey(), (int) $purchase->company_id);
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->getKey(),
            'product_id' => $product->getKey(),
            'quantity' => 4,
            'subtotal' => '29.00',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->getKey(),
            'source_type' => Purchase::class,
            'source_id' => $purchase->getKey(),
            'type' => 'in',
            'quantity' => 4,
        ]);

        $this->get(route('purchases.show', $purchase))->assertOk();
        $this->get(route('purchases.edit', $purchase))->assertOk();
        $this->put(route('purchases.update', $purchase), [
            'supplier_id' => $supplier->getKey(),
            'purchase_date' => now()->toDateString(),
            'products' => [
                [
                    'product_id' => $product->getKey(),
                    'quantity' => 2,
                    'unit_price' => '8.00',
                ],
            ],
        ])->assertRedirect(route('purchases.index'));

        $purchase->refresh();
        $product->refresh();

        $this->assertSame(5, $product->stock);
        $this->assertSame('16.00', $purchase->total_amount);
        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->getKey(),
            'quantity' => 2,
            'subtotal' => '16.00',
        ]);

        $this->delete(route('purchases.destroy', $purchase))
            ->assertRedirect(route('purchases.index'));

        $product->refresh();
        $this->assertSame(3, $product->stock);
        $this->assertDatabaseMissing('purchases', ['id' => $purchase->getKey()]);
        $this->assertDatabaseMissing('purchase_items', ['purchase_id' => $purchase->getKey()]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->getKey(),
            'source_type' => Purchase::class,
            'source_id' => $purchase->getKey(),
            'type' => 'out',
            'quantity' => 2,
        ]);
    }

    public function test_purchase_index_filters_by_number_supplier_status_dates_and_company_scope(): void
    {
        [$adminA] = $this->createAdmin();
        $this->actingAs($adminA);
        $supplierA = Supplier::create(['name' => 'Northwind Supplies']);
        $targetPurchase = $this->createPurchaseRecord(
            $supplierA,
            'PUR-ALPHA-001',
            '2026-03-10',
            'received'
        );
        $pendingPurchase = $this->createPurchaseRecord(
            $supplierA,
            'PUR-PENDING-002',
            '2026-03-05',
            'pending'
        );
        $oldPurchase = $this->createPurchaseRecord(
            $supplierA,
            'PUR-OLD-003',
            '2026-02-28',
            'received'
        );

        [$adminB] = $this->createAdmin();
        $this->actingAs($adminB);
        $supplierB = Supplier::create(['name' => 'Northwind Supplies']);
        $foreignPurchase = $this->createPurchaseRecord(
            $supplierB,
            'PUR-FOREIGN-004',
            '2026-03-10',
            'received'
        );

        $this->actingAs($adminA)
            ->get(route('purchases.index', [
                'search' => 'Northwind',
                'status' => 'received',
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-10',
            ]))
            ->assertOk()
            ->assertSeeText($targetPurchase->purchase_number)
            ->assertDontSeeText($pendingPurchase->purchase_number)
            ->assertDontSeeText($oldPurchase->purchase_number)
            ->assertDontSeeText($foreignPurchase->purchase_number)
            ->assertSeeText('Effacer');

        $this->get(route('purchases.index', [
            'search' => $targetPurchase->purchase_number,
        ]))
            ->assertOk()
            ->assertSeeText($targetPurchase->purchase_number)
            ->assertDontSeeText($pendingPurchase->purchase_number);
    }

    public function test_purchase_index_validates_search_status_and_date_filters(): void
    {
        [$admin] = $this->createAdmin();
        $this->actingAs($admin);

        $this->from(route('purchases.index'))
            ->get(route('purchases.index', [
                'search' => str_repeat('x', 101),
            ]))
            ->assertRedirect(route('purchases.index'))
            ->assertSessionHasErrors('search');

        $this->from(route('purchases.index'))
            ->get(route('purchases.index', ['status' => 'paid']))
            ->assertRedirect(route('purchases.index'))
            ->assertSessionHasErrors('status');

        $this->from(route('purchases.index'))
            ->get(route('purchases.index', [
                'start_date' => '2026-03-11',
                'end_date' => '2026-03-01',
            ]))
            ->assertRedirect(route('purchases.index'))
            ->assertSessionHasErrors(['start_date', 'end_date']);

        $this->from(route('purchases.index'))
            ->get(route('purchases.index', ['start_date' => '2026-03-01']))
            ->assertRedirect(route('purchases.index'))
            ->assertSessionHasErrors('end_date');
    }

    private function createAdmin(): array
    {
        $company = Company::create([
            'name' => 'Inventory company',
            'slug' => 'inventory-company-'.Str::uuid(),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->company()->associate($company);
        $admin->save();

        return [$admin, $company];
    }

    private function createProduct(
        string $reference,
        int $stock,
        float $sellingPrice
    ): Product {
        $category = Category::create(['name' => 'Inventory category']);

        return Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Inventory product',
            'reference' => $reference,
            'purchase_price' => 5,
            'selling_price' => $sellingPrice,
            'stock' => $stock,
            'stock_alert_threshold' => 1,
        ]);
    }

    private function createSaleRecord(
        Client $client,
        string $saleNumber,
        string $date,
        string $status
    ): Sale {
        return Sale::create([
            'client_id' => $client->getKey(),
            'sale_number' => $saleNumber,
            'sale_date' => $date,
            'total_amount' => 100,
            'status' => $status,
        ]);
    }

    private function createPurchaseRecord(
        Supplier $supplier,
        string $purchaseNumber,
        string $date,
        string $status
    ): Purchase {
        return Purchase::create([
            'supplier_id' => $supplier->getKey(),
            'purchase_number' => $purchaseNumber,
            'purchase_date' => $date,
            'total_amount' => 100,
            'status' => $status,
        ]);
    }
}
