<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException as AccessAuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_read_or_create_business_data(): void
    {
        $company = Company::create([
            'name' => 'Company A',
            'slug' => 'company-a-'.Str::uuid(),
        ]);
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
            'company_id' => $company->getKey(),
        ]);

        $this->actingAs($admin);
        $category = Category::create(['name' => 'Category']);
        Product::create([
            'category_id' => $category->getKey(),
            'name' => 'Private product',
            'reference' => 'PRIVATE-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 1,
            'stock_alert_threshold' => 1,
        ]);

        Auth::logout();

        $this->assertSame(0, Product::query()->count());

        try {
            Product::create([
                'category_id' => $category->getKey(),
                'name' => 'Guest product',
                'reference' => 'GUEST-001',
                'purchase_price' => 10,
                'selling_price' => 20,
                'stock' => 1,
                'stock_alert_threshold' => 1,
            ]);
            $this->fail('La création sans utilisateur aurait dû être refusée.');
        } catch (AccessAuthorizationException) {
            $this->assertDatabaseMissing('products', ['reference' => 'GUEST-001']);
        }
    }

    public function test_registration_rejects_an_expired_join_code(): void
    {
        $company = Company::create([
            'name' => 'Existing company',
            'slug' => 'existing-'.Str::uuid(),
        ]);
        $company->forceFill([
            'join_code_hash' => hash('sha256', 'EXPIRED123'),
            'join_code_expires_at' => now()->subMinute(),
        ])->save();

        $this->from('/register')
            ->post('/register', [
                'join_code' => 'EXPIRED123',
                'name' => 'Late employee',
                'email' => 'late@example.com',
                'password' => 'Secure-password-123',
                'password_confirmation' => 'Secure-password-123',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('join_code');

        $this->assertDatabaseMissing('users', ['email' => 'late@example.com']);
    }

    public function test_join_code_is_single_use(): void
    {
        $company = Company::create([
            'name' => 'Existing company',
            'slug' => 'existing-'.Str::uuid(),
        ]);
        $company->forceFill([
            'join_code_hash' => hash('sha256', 'ONETIME123'),
            'join_code_expires_at' => now()->addHours(24),
        ])->save();

        $this->post('/register', [
            'join_code' => 'ONETIME123',
            'name' => 'First employee',
            'email' => 'first@example.com',
            'password' => 'Secure-password-123',
            'password_confirmation' => 'Secure-password-123',
        ])->assertRedirect('/dashboard');

        Auth::logout();

        $this->from('/register')
            ->post('/register', [
                'join_code' => 'ONETIME123',
                'name' => 'Second employee',
                'email' => 'second@example.com',
                'password' => 'Secure-password-123',
                'password_confirmation' => 'Secure-password-123',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('join_code');

        $this->assertDatabaseMissing('users', ['email' => 'second@example.com']);
    }
}
