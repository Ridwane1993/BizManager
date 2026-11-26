<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_company_and_an_admin_account(): void
    {
        $this->post('/register', [
            'company_name' => 'Nouvelle entreprise',
            'name' => 'First admin',
            'email' => 'first-admin@example.com',
            'password' => 'Secure-password-123',
            'password_confirmation' => 'Secure-password-123',
        ])->assertRedirect('/dashboard');

        $company = Company::where('name', 'Nouvelle entreprise')->firstOrFail();

        $this->assertAuthenticated();
        $this->assertSame('admin', auth()->user()->role);
        $this->assertSame($company->getKey(), auth()->user()->company_id);
        $this->assertDatabaseHas('users', [
            'email' => 'first-admin@example.com',
            'company_id' => $company->getKey(),
            'role' => 'admin',
        ]);

        // Le modèle User reste volontairement non scopé globalement pour Fortify.
        $this->assertSame(1, User::query()->count());
    }

    public function test_registration_with_company_join_code_joins_existing_company(): void
    {
        $company = Company::create([
            'name' => 'Existing company',
            'slug' => 'existing-company-'.Str::uuid(),
        ]);
        $company->forceFill([
            'join_code_hash' => hash('sha256', 'JOINCODE12'),
        ])->save();
        $companyCount = Company::count();

        $this->post('/register', [
            'join_code' => 'joincode12',
            'name' => 'New employee',
            'email' => 'joined-employee@example.com',
            'password' => 'Secure-password-123',
            'password_confirmation' => 'Secure-password-123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertSame('employee', auth()->user()->role);
        $this->assertSame($company->getKey(), auth()->user()->company_id);
        $this->assertSame($companyCount, Company::count());
    }

    public function test_registration_rejects_an_invalid_company_join_code(): void
    {
        $this->from('/register')
            ->post('/register', [
                'join_code' => 'INVALID123',
                'name' => 'New employee',
                'email' => 'invalid-code@example.com',
                'password' => 'Secure-password-123',
                'password_confirmation' => 'Secure-password-123',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('join_code');

        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-code@example.com',
        ]);
    }
}
