<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_crud_tracks_stock_and_protects_its_history(): void
    {
        [$company, $admin] = $this->createAdmin('Product company');
        $this->actingAs($admin);
        $category = Category::create(['name' => 'Product category']);
        $productData = $this->productData($category, 'PRODUCT-001', 5);

        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.create'))->assertOk();
        $this->post(route('products.store'), $productData)
            ->assertRedirect(route('products.index'));

        $product = Product::where('reference', 'PRODUCT-001')->firstOrFail();

        $this->assertSame((int) $company->getKey(), (int) $product->company_id);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->getKey(),
            'source_type' => Product::class,
            'source_id' => $product->getKey(),
            'type' => 'in',
            'quantity' => 5,
        ]);
        $this->get(route('products.show', $product))->assertOk();
        $this->get(route('products.edit', $product))->assertOk();

        $updatedData = $this->productData($category, 'PRODUCT-001', 7);
        $updatedData['name'] = 'Updated product';

        $this->put(route('products.update', $product), $updatedData)
            ->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertSame('Updated product', $product->name);
        $this->assertSame(7, $product->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->getKey(),
            'type' => 'adjustment',
            'quantity' => 2,
            'source_type' => null,
            'source_id' => null,
        ]);

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->getKey()]);
    }

    public function test_product_form_rejects_negative_stock_without_persisting(): void
    {
        [, $admin] = $this->createAdmin('Validation company');
        $this->actingAs($admin);
        $category = Category::create(['name' => 'Validation category']);

        $this->from(route('products.create'))
            ->post(route('products.store'), $this->productData($category, 'INVALID-001', -1))
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors('stock');

        $this->assertDatabaseMissing('products', ['reference' => 'INVALID-001']);
    }

    public function test_product_references_are_unique_per_company(): void
    {
        [, $adminA] = $this->createAdmin('Product company A');
        $this->actingAs($adminA);
        $categoryA = Category::create(['name' => 'Company A category']);
        $productDataA = $this->productData($categoryA, 'SHARED-001', 0);

        $this->post(route('products.store'), $productDataA)
            ->assertRedirect(route('products.index'));

        $this->from(route('products.create'))
            ->post(route('products.store'), $productDataA)
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors('reference');

        [, $adminB] = $this->createAdmin('Product company B');
        $this->actingAs($adminB);
        $categoryB = Category::create(['name' => 'Company B category']);

        $this->post(
            route('products.store'),
            $this->productData($categoryB, 'SHARED-001', 0)
        )->assertRedirect(route('products.index'));

        $this->assertDatabaseCount('products', 2);
    }

    public function test_product_without_stock_history_can_be_deleted(): void
    {
        [, $admin] = $this->createAdmin('Deletion company');
        $this->actingAs($admin);
        $category = Category::create(['name' => 'Deletion category']);
        $this->post(
            route('products.store'),
            $this->productData($category, 'DELETE-001', 0)
        )->assertRedirect(route('products.index'));

        $product = Product::where('reference', 'DELETE-001')->firstOrFail();

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->getKey()]);
    }

    public function test_product_index_filters_by_search_category_and_low_stock_with_company_scope(): void
    {
        [, $adminA] = $this->createAdmin('Product filter company A');
        $this->actingAs($adminA);
        $categoryA = Category::create(['name' => 'Product filter category']);
        $targetProduct = $this->createProductRecord(
            $categoryA,
            'Alpha laptop',
            'ALPHA-001',
            2,
            2
        );
        $this->createProductRecord(
            $categoryA,
            'Alpha monitor',
            'ALPHA-002',
            10,
            2
        );

        [, $adminB] = $this->createAdmin('Product filter company B');
        $this->actingAs($adminB);
        $categoryB = Category::create(['name' => 'Product filter category']);
        $foreignProduct = $this->createProductRecord(
            $categoryB,
            'Alpha foreign laptop',
            'ALPHA-FOREIGN',
            0,
            1
        );

        $this->actingAs($adminA)
            ->get(route('products.index', [
                'search' => 'Alpha',
                'category_id' => $categoryA->getKey(),
                'low_stock' => '1',
            ]))
            ->assertOk()
            ->assertSeeText($targetProduct->name)
            ->assertDontSeeText('Alpha monitor')
            ->assertDontSeeText($foreignProduct->name)
            ->assertViewHas('products', static fn ($products): bool => $products->total() === 1);

        $this->get(route('products.index', ['search' => 'ALPHA-001']))
            ->assertOk()
            ->assertSeeText($targetProduct->reference);
    }

    public function test_product_index_validates_search_category_and_low_stock_filters(): void
    {
        [, $adminA] = $this->createAdmin('Product filter validation A');
        $this->actingAs($adminA);
        [, $adminB] = $this->createAdmin('Product filter validation B');
        $this->actingAs($adminB);
        $foreignCategory = Category::create(['name' => 'Foreign filter category']);
        $this->actingAs($adminA);

        $this->from(route('products.index'))
            ->get(route('products.index', [
                'search' => str_repeat('x', 101),
            ]))
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors('search');

        $this->from(route('products.index'))
            ->get(route('products.index', [
                'category_id' => $foreignCategory->getKey(),
            ]))
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors('category_id');

        $this->from(route('products.index'))
            ->get(route('products.index', ['low_stock' => 'maybe']))
            ->assertRedirect(route('products.index'))
            ->assertSessionHasErrors('low_stock');
    }

    public function test_product_index_paginates_and_preserves_filters(): void
    {
        [, $admin] = $this->createAdmin('Product pagination company');
        $this->actingAs($admin);
        $category = Category::create(['name' => 'Pagination category']);

        for ($index = 1; $index <= 16; $index++) {
            $this->createProductRecord(
                $category,
                sprintf('Paginated product %02d', $index),
                sprintf('PAGE-%03d', $index),
                0,
                1
            );
        }

        $this->get(route('products.index', [
            'search' => 'Paginated product',
            'category_id' => $category->getKey(),
            'low_stock' => '1',
        ]))
            ->assertOk()
            ->assertSee('page=2')
            ->assertSee('search=Paginated')
            ->assertViewHas('products', static fn ($products): bool => $products->total() === 16
                && $products->count() === 15
            );

        $this->get(route('products.index', [
            'search' => 'Paginated product',
            'category_id' => $category->getKey(),
            'low_stock' => '1',
            'page' => 2,
        ]))
            ->assertOk()
            ->assertViewHas('products', static fn ($products): bool => $products->currentPage() === 2
                && $products->total() === 16
                && $products->count() === 1
            );
    }

    private function productData(Category $category, string $reference, int $stock): array
    {
        return [
            'category_id' => $category->getKey(),
            'name' => 'Test product',
            'reference' => $reference,
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => $stock,
            'stock_alert_threshold' => 2,
        ];
    }

    private function createProductRecord(
        Category $category,
        string $name,
        string $reference,
        int $stock,
        int $threshold
    ): Product {
        return Product::create([
            'category_id' => $category->getKey(),
            'name' => $name,
            'reference' => $reference,
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => $stock,
            'stock_alert_threshold' => $threshold,
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
