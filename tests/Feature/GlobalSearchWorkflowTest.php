<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GlobalSearchWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_finds_company_scoped_suppliers_purchases_and_expenses(): void
    {
        $companyA = $this->createCompany('Search company A');
        $adminA = $this->createAdmin($companyA, 'search-a@example.test');
        $this->actingAs($adminA);

        $supplier = Supplier::create([
            'name' => 'Transit Supplies',
            'email' => 'orders@transit.example.test',
        ]);
        $purchase = Purchase::create([
            'supplier_id' => $supplier->getKey(),
            'purchase_number' => 'PUR-SEARCH-001',
            'purchase_date' => now()->toDateString(),
            'total_amount' => 420,
            'status' => 'received',
        ]);
        $expense = Expense::create([
            'category' => 'Transit expense',
            'amount' => 125,
            'expense_date' => now()->toDateString(),
            'description' => 'Monthly transport',
        ]);

        $companyB = $this->createCompany('Search company B');
        $adminB = $this->createAdmin($companyB, 'search-b@example.test');
        $this->actingAs($adminB);

        $hiddenSupplier = Supplier::create([
            'name' => 'Hidden Transit Supplier',
        ]);
        Purchase::create([
            'supplier_id' => $hiddenSupplier->getKey(),
            'purchase_number' => 'PUR-HIDDEN-001',
            'purchase_date' => now()->toDateString(),
            'total_amount' => 999,
            'status' => 'received',
        ]);
        Expense::create([
            'category' => 'Hidden Transit expense',
            'amount' => 999,
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($adminA)
            ->get(route('search.index', ['q' => 'Transit']))
            ->assertOk()
            ->assertSee('Fournisseurs')
            ->assertSee('Transit Supplies')
            ->assertSee(route('suppliers.show', $supplier))
            ->assertSee('flex-column flex-sm-row', false)
            ->assertSee('Achats')
            ->assertSee('PUR-SEARCH-001')
            ->assertSee(route('purchases.show', $purchase))
            ->assertSee('Dépenses')
            ->assertSee('Transit expense')
            ->assertSee('Monthly transport')
            ->assertSee('125 FCFA')
            ->assertSee(route('expenses.show', $expense))
            ->assertDontSee('Hidden Transit Supplier')
            ->assertDontSee('PUR-HIDDEN-001')
            ->assertDontSee('Hidden Transit expense');

        $this->get(route('search.index', ['q' => 'PUR-SEARCH-001']))
            ->assertOk()
            ->assertSee('PUR-SEARCH-001')
            ->assertSee(route('purchases.show', $purchase));

        $this->get(route('search.index', ['q' => 'Monthly transport']))
            ->assertOk()
            ->assertSee('Transit expense')
            ->assertSee(route('expenses.show', $expense));
    }

    public function test_global_search_rejects_terms_longer_than_one_hundred_characters(): void
    {
        $company = $this->createCompany('Search validation company');
        $admin = $this->createAdmin($company, 'search-validation@example.test');

        $this->actingAs($admin)
            ->from(route('search.index'))
            ->get(route('search.index', ['q' => str_repeat('x', 101)]))
            ->assertRedirect(route('search.index'))
            ->assertSessionHasErrors('q');
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
