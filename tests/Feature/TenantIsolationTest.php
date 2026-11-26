<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_queries_and_creation_are_limited_to_the_signed_in_company(): void
    {
        $companyA = $this->createCompany('Company A');
        $adminA = $this->createAdmin($companyA, 'admin-a@example.com');

        $companyB = $this->createCompany('Company B');
        $adminB = $this->createAdmin($companyB, 'admin-b@example.com');

        $this->actingAs($adminB);
        $categoryB = Category::create(['name' => 'Private category']);
        Product::create([
            'category_id' => $categoryB->getKey(),
            'name' => 'Private product',
            'reference' => 'PRIVATE-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 3,
            'stock_alert_threshold' => 1,
        ]);

        $this->actingAs($adminA)
            ->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Private product');

        $this->actingAs($adminA)
            ->get(route('search.index', ['q' => 'PRIVATE-001']))
            ->assertOk()
            ->assertDontSee('Private product');

        $this->actingAs($adminA)
            ->post(route('products.store'), [
                'category_id' => $categoryB->getKey(),
                'name' => 'Cross-company product',
                'reference' => 'CROSS-001',
                'purchase_price' => 10,
                'selling_price' => 20,
                'stock' => 1,
                'stock_alert_threshold' => 1,
            ])
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('products', [
            'reference' => 'CROSS-001',
        ]);
    }

    public function test_user_without_company_cannot_be_created_and_foreign_user_sees_no_data(): void
    {
        $company = $this->createCompany('Company A');
        $admin = $this->createAdmin($company, 'admin-a@example.com');

        $this->actingAs($admin);
        $category = Category::create(['name' => 'Visible only to Company A']);
        Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Company A product',
            'reference' => 'COMPANY-A-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 3,
            'stock_alert_threshold' => 1,
        ]);

        $otherCompany = $this->createCompany('Company B');
        $otherUser = $this->createAdmin($otherCompany, 'other@example.com');

        $this->actingAs($otherUser)
            ->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Company A product');
    }

    public function test_low_stock_filter_only_returns_products_below_their_company_threshold(): void
    {
        $company = $this->createCompany('Company A');
        $admin = $this->createAdmin($company, 'admin-a@example.com');

        $this->actingAs($admin);
        $category = Category::create(['name' => 'Stock category']);

        Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Low stock product',
            'reference' => 'LOW-STOCK-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 1,
            'stock_alert_threshold' => 2,
        ]);
        Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Available stock product',
            'reference' => 'AVAILABLE-STOCK-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 3,
            'stock_alert_threshold' => 2,
        ]);

        $this->get(route('products.index', ['low_stock' => 1]))
            ->assertOk()
            ->assertSee('Low stock product')
            ->assertDontSee('Available stock product');
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
