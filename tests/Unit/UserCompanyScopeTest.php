<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\User;
use App\Support\UserCompanyScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class UserCompanyScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_for_returns_the_users_company(): void
    {
        $company = $this->company('Scope company');
        $user = User::factory()->create(['company_id' => $company->getKey()]);

        $this->assertTrue(
            $company->is(UserCompanyScope::companyFor($user))
        );
    }

    public function test_company_for_rejects_a_user_without_company(): void
    {
        $user = User::make(['company_id' => null]);

        try {
            UserCompanyScope::companyFor($user);
            $this->fail('Un utilisateur sans entreprise doit être refusé.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_belongs_to_compares_company_ids(): void
    {
        $companyA = $this->company('Company A');
        $companyB = $this->company('Company B');
        $actor = User::factory()->create(['company_id' => $companyA->getKey()]);
        $sameCompany = User::factory()->create([
            'company_id' => $companyA->getKey(),
        ]);
        $otherCompany = User::factory()->create([
            'company_id' => $companyB->getKey(),
        ]);

        $this->assertTrue(
            UserCompanyScope::belongsTo($actor, $sameCompany)
        );
        $this->assertFalse(
            UserCompanyScope::belongsTo($actor, $otherCompany)
        );
        $this->assertFalse(
            UserCompanyScope::belongsTo(
                User::make(['company_id' => null]),
                $sameCompany
            )
        );
    }

    public function test_query_for_only_returns_users_from_the_company(): void
    {
        $companyA = $this->company('Query company A');
        $companyB = $this->company('Query company B');
        $userA = User::factory()->create(['company_id' => $companyA->getKey()]);
        $userB = User::factory()->create(['company_id' => $companyB->getKey()]);

        $ids = UserCompanyScope::queryFor($companyA)
            ->pluck('id')
            ->all();

        $this->assertContains($userA->getKey(), $ids);
        $this->assertNotContains($userB->getKey(), $ids);
    }

    public function test_ensure_belongs_to_aborts_with_404_on_another_company(): void
    {
        $companyA = $this->company('Ensure company A');
        $companyB = $this->company('Ensure company B');
        $userB = User::factory()->create(['company_id' => $companyB->getKey()]);

        try {
            UserCompanyScope::ensureBelongsTo($userB, $companyA);
            $this->fail('Un utilisateur d’une autre entreprise doit être masqué.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    private function company(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::uuid(),
        ]);
    }
}
