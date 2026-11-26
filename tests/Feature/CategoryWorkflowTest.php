<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_crud_preserves_product_relation_and_prevents_unsafe_deletion(): void
    {
        [$company, $admin] = $this->createAdmin('Category company');
        $this->actingAs($admin);

        $this->get(route('categories.index'))->assertOk();
        $this->get(route('categories.create'))->assertOk();
        $this->post(route('categories.store'), [
            'name' => 'Hardware',
            'description' => 'Company catalogue',
        ])->assertRedirect(route('categories.index'));

        $category = Category::where('name', 'Hardware')->firstOrFail();
        $this->assertSame((int) $company->getKey(), (int) $category->company_id);
        $this->assertDatabaseHas('categories', [
            'id' => $category->getKey(),
            'company_id' => $company->getKey(),
            'name' => 'Hardware',
        ]);
        $this->get(route('categories.edit', $category))->assertOk();

        $this->put(route('categories.update', $category), [
            'name' => 'Equipment',
            'description' => 'Updated catalogue',
        ])->assertRedirect(route('categories.index'));

        $category->refresh();
        $this->assertSame('Equipment', $category->name);
        $this->assertSame('Updated catalogue', $category->description);

        $product = Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Related product',
            'reference' => 'CATEGORY-REL-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 0,
            'stock_alert_threshold' => 2,
        ]);

        $this->assertTrue($category->products()->whereKey($product->getKey())->exists());
        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->getKey()]);

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'))
            ->assertSessionHas('success');
        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->getKey()]);
    }

    public function test_category_names_are_unique_per_company_and_not_visible_across_companies(): void
    {
        [, $adminA] = $this->createAdmin('Category company A');
        $this->actingAs($adminA);

        $this->post(route('categories.store'), [
            'name' => 'Services',
            'description' => 'Company A only',
        ])->assertRedirect(route('categories.index'));
        $categoryA = Category::where('name', 'Services')->firstOrFail();

        $this->from(route('categories.create'))
            ->post(route('categories.store'), [
                'name' => 'Services',
                'description' => 'Duplicate in A',
            ])
            ->assertRedirect(route('categories.create'))
            ->assertSessionHasErrors('name');

        $this->put(route('categories.update', $categoryA), [
            'name' => 'Services',
            'description' => 'Company A updated',
        ])->assertRedirect(route('categories.index'));

        [, $adminB] = $this->createAdmin('Category company B');
        $this->actingAs($adminB);
        $this->post(route('categories.store'), [
            'name' => 'Services',
            'description' => 'Company B only',
        ])->assertRedirect(route('categories.index'));

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Services')
            ->assertSee('Company B only')
            ->assertDontSee('Company A updated');
        $this->get(route('categories.edit', $categoryA))->assertNotFound();

        $this->assertDatabaseCount('categories', 2);
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
