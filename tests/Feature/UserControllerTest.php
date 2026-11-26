<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_only_sees_users_from_their_company(): void
    {
        $company = $this->createCompany('Company A');
        $admin = $this->createAdmin($company, 'admin-a@example.com');
        $member = User::factory()->create([
            'name' => 'Company A member',
            'email' => 'member-a@example.com',
            'role' => 'employee',
        ]);
        $member->company()->associate($company);
        $member->save();

        $otherCompany = $this->createCompany('Company B');
        $otherMember = User::factory()->create([
            'name' => 'Company B member',
            'email' => 'member-b@example.com',
            'role' => 'employee',
        ]);
        $otherMember->company()->associate($otherCompany);
        $otherMember->save();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Company A member')
            ->assertDontSee('Company B member');
    }

    public function test_created_user_is_assigned_to_the_admins_company(): void
    {
        $company = $this->createCompany('Company A');
        $admin = $this->createAdmin($company, 'admin-a@example.com');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'New employee',
                'email' => 'new-employee@example.com',
                'role' => 'employee',
                'password' => 'secure-password',
                'password_confirmation' => 'secure-password',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-employee@example.com',
            'company_id' => $company->getKey(),
        ]);
    }

    public function test_company_join_code_is_only_stored_as_a_hash(): void
    {
        $company = $this->createCompany('Company A');
        $admin = $this->createAdmin($company, 'admin-a@example.com');

        $response = $this->actingAs($admin)
            ->post(route('company.join-code'));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('company_join_code');

        $joinCode = session('company_join_code');
        $company->refresh();

        $this->assertIsString($joinCode);
        $this->assertSame(
            hash('sha256', $joinCode),
            $company->join_code_hash
        );
        $this->assertNotSame($joinCode, $company->join_code_hash);
    }

    public function test_admin_cannot_access_or_change_users_from_another_company(): void
    {
        $company = $this->createCompany('Company A');
        $admin = $this->createAdmin($company, 'admin-a@example.com');

        $otherCompany = $this->createCompany('Company B');
        $otherUser = User::factory()->create([
            'email' => 'other-user@example.com',
            'role' => 'employee',
        ]);
        $otherUser->company()->associate($otherCompany);
        $otherUser->save();

        $this->actingAs($admin)
            ->get(route('users.show', $otherUser))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('users.edit', $otherUser))
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('users.update', $otherUser), [
                'name' => 'Changed name',
                'email' => 'other-user@example.com',
                'role' => 'employee',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $otherUser))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $otherUser->getKey(),
            'name' => $otherUser->name,
            'company_id' => $otherCompany->getKey(),
        ]);
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
