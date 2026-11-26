<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_and_purchases_reject_foreign_company_relations(): void
    {
        [, $adminA] = $this->createAdmin('Commerce company A');
        [, $adminB] = $this->createAdmin('Commerce company B');

        $this->actingAs($adminB);
        $clientB = Client::create(['name' => 'Private client B']);
        $supplierB = Supplier::create(['name' => 'Private supplier B']);
        $productB = $this->createProduct('Private product B', 'COMMERCE-B-001');

        $this->actingAs($adminA);
        $clientA = Client::create(['name' => 'Private client A']);
        $supplierA = Supplier::create(['name' => 'Private supplier A']);
        $productA = $this->createProduct('Private product A', 'COMMERCE-A-001');

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'client_id' => $clientB->getKey(),
                'sale_date' => now()->toDateString(),
                'products' => [
                    ['product_id' => $productA->getKey(), 'quantity' => 1],
                ],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('client_id');

        $this->from(route('sales.create'))
            ->post(route('sales.store'), [
                'client_id' => $clientA->getKey(),
                'sale_date' => now()->toDateString(),
                'products' => [
                    ['product_id' => $productB->getKey(), 'quantity' => 1],
                ],
            ])
            ->assertRedirect(route('sales.create'))
            ->assertSessionHasErrors('products.0.product_id');

        $this->from(route('purchases.create'))
            ->post(route('purchases.store'), [
                'supplier_id' => $supplierB->getKey(),
                'purchase_date' => now()->toDateString(),
                'products' => [
                    [
                        'product_id' => $productA->getKey(),
                        'quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertRedirect(route('purchases.create'))
            ->assertSessionHasErrors('supplier_id');

        $this->from(route('purchases.create'))
            ->post(route('purchases.store'), [
                'supplier_id' => $supplierA->getKey(),
                'purchase_date' => now()->toDateString(),
                'products' => [
                    [
                        'product_id' => $productB->getKey(),
                        'quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertRedirect(route('purchases.create'))
            ->assertSessionHasErrors('products.0.product_id');

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('purchases', 0);
        $this->assertDatabaseCount('purchase_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    private function createProduct(string $name, string $reference): Product
    {
        $category = Category::create(['name' => $name.' category']);

        return Product::create([
            'category_id' => $category->getKey(),
            'name' => $name,
            'reference' => $reference,
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 5,
            'stock_alert_threshold' => 1,
        ]);
    }

    private function createAdmin(string $companyName): array
    {
        $company = Company::create([
            'name' => $companyName,
            'slug' => Str::slug($companyName).'-'.Str::uuid(),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->company()->associate($company);
        $admin->save();

        return [$company, $admin];
    }
}
