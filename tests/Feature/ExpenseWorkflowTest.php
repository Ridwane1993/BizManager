<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_crud_persists_company_scoped_category_relation(): void
    {
        $company = $this->createCompany('Expense company');
        $admin = $this->createAdmin($company);

        $this->actingAs($admin)
            ->get(route('expenses.create'))
            ->assertOk();

        $this->post(route('expenses.store'), [
            'category' => 'Électricité',
            'amount' => '125.50',
            'expense_date' => now()->toDateString(),
            'description' => 'Facture mensuelle',
        ])->assertRedirect(route('expenses.index'));

        $expense = Expense::firstOrFail();
        $this->assertSame('Électricité', $expense->expenseCategory->name);
        $this->assertSame(
            (int) $company->getKey(),
            (int) $expense->expenseCategory->company_id
        );

        $this->get(route('expenses.edit', $expense))->assertOk();
        $this->get(route('expenses.show', $expense))->assertOk();

        $this->put(route('expenses.update', $expense), [
            'category' => 'Transport',
            'amount' => '50.00',
            'expense_date' => now()->toDateString(),
            'description' => 'Déplacement',
        ])->assertRedirect(route('expenses.index'));

        $expense->refresh();
        $this->assertSame('Transport', $expense->category);
        $this->assertSame('Transport', $expense->expenseCategory->name);
        $this->assertDatabaseHas('expense_categories', [
            'company_id' => $company->getKey(),
            'name' => 'Transport',
        ]);

        $this->delete(route('expenses.destroy', $expense))
            ->assertRedirect(route('expenses.index'));

        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->getKey(),
        ]);
    }

    public function test_old_fenced_views_and_stock_forms_render_without_blade_errors(): void
    {
        $company = $this->createCompany('View company');
        $admin = $this->createAdmin($company);
        $this->actingAs($admin);

        $this->get(route('expenses.create'))->assertOk();
        $this->get(route('purchases.create'))->assertOk();
        $this->get(route('stock-movements.create'))->assertOk();

        $category = Category::create(['name' => 'View category']);
        $product = Product::create([
            'category_id' => $category->getKey(),
            'name' => 'View product',
            'reference' => 'VIEW-001',
            'purchase_price' => 10,
            'selling_price' => 20,
            'stock' => 2,
            'stock_alert_threshold' => 1,
        ]);

        $expense = Expense::create([
            'category' => 'View expense',
            'amount' => 10,
            'expense_date' => now()->toDateString(),
        ]);
        $movement = StockMovement::create([
            'product_id' => $product->getKey(),
            'type' => 'in',
            'quantity' => 1,
            'description' => 'View movement',
        ]);

        $this->get(route('expenses.edit', $expense))->assertOk();
        $this->get(route('expenses.show', $expense))->assertOk();
        $this->get(route('stock-movements.edit', $movement))->assertOk();
        $this->get(route('stock-movements.show', $movement))->assertOk();
    }

    public function test_invalid_expense_amount_is_rejected_without_persisting_a_record(): void
    {
        $company = $this->createCompany('Validation company');
        $admin = $this->createAdmin($company);

        $this->actingAs($admin)
            ->post(route('expenses.store'), [
                'category' => 'Utilities',
                'amount' => '0',
                'expense_date' => now()->toDateString(),
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_expense_routes_cannot_access_another_companys_record(): void
    {
        $companyA = $this->createCompany('Expense owner company');
        $adminA = $this->createAdmin($companyA);
        $companyB = $this->createCompany('Expense other company');
        $adminB = $this->createAdmin($companyB);

        $this->actingAs($adminB);
        $expense = Expense::create([
            'category' => 'Private expense',
            'amount' => 80,
            'expense_date' => now()->toDateString(),
        ]);

        $this->actingAs($adminA)
            ->get(route('expenses.index'))
            ->assertOk()
            ->assertDontSee('Private expense');

        $this->get(route('expenses.show', $expense))->assertNotFound();
        $this->put(route('expenses.update', $expense), [
            'category' => 'Changed expense',
            'amount' => 100,
            'expense_date' => now()->toDateString(),
        ])->assertNotFound();
        $this->delete(route('expenses.destroy', $expense))->assertNotFound();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->getKey(),
            'category' => 'Private expense',
        ]);
    }

    public function test_expense_index_filters_by_search_category_and_inclusive_dates_with_company_scope(): void
    {
        $companyA = $this->createCompany('Expense filter company A');
        $adminA = $this->createAdmin($companyA);
        $this->actingAs($adminA);

        $utilities = ExpenseCategory::create(['name' => 'Utilities']);
        $targetExpense = Expense::create([
            'category' => $utilities->name,
            'expense_category_id' => $utilities->getKey(),
            'amount' => 75,
            'expense_date' => '2026-03-10',
            'description' => 'March electricity bill',
        ]);

        $transport = ExpenseCategory::create(['name' => 'Transport']);
        Expense::create([
            'category' => $transport->name,
            'expense_category_id' => $transport->getKey(),
            'amount' => 20,
            'expense_date' => '2026-03-10',
            'description' => 'March electricity taxi',
        ]);
        Expense::create([
            'category' => $utilities->name,
            'expense_category_id' => $utilities->getKey(),
            'amount' => 40,
            'expense_date' => '2026-02-28',
            'description' => 'February electricity bill',
        ]);

        $companyB = $this->createCompany('Expense filter company B');
        $adminB = $this->createAdmin($companyB);
        $this->actingAs($adminB);
        $foreignCategory = ExpenseCategory::create(['name' => 'Utilities']);
        $foreignExpense = Expense::create([
            'category' => $foreignCategory->name,
            'expense_category_id' => $foreignCategory->getKey(),
            'amount' => 900,
            'expense_date' => '2026-03-10',
            'description' => 'Foreign electricity bill',
        ]);

        $this->actingAs($adminA)
            ->get(route('expenses.index', [
                'search' => 'electricity',
                'category_id' => $utilities->getKey(),
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-10',
            ]))
            ->assertOk()
            ->assertSeeText($targetExpense->description)
            ->assertDontSeeText('electricity taxi')
            ->assertDontSeeText('February electricity bill')
            ->assertDontSeeText($foreignExpense->description)
            ->assertSeeText('Effacer');
    }

    public function test_expense_index_validates_search_category_and_date_filters(): void
    {
        $companyA = $this->createCompany('Expense filter validation A');
        $adminA = $this->createAdmin($companyA);
        $companyB = $this->createCompany('Expense filter validation B');
        $adminB = $this->createAdmin($companyB);
        $this->actingAs($adminB);
        $foreignCategory = ExpenseCategory::create(['name' => 'Foreign category']);

        $this->actingAs($adminA);
        $this->from(route('expenses.index'))
            ->get(route('expenses.index', [
                'search' => str_repeat('x', 101),
            ]))
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors('search');

        $this->from(route('expenses.index'))
            ->get(route('expenses.index', [
                'category_id' => $foreignCategory->getKey(),
            ]))
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors('category_id');

        $this->from(route('expenses.index'))
            ->get(route('expenses.index', [
                'start_date' => '2026-03-11',
                'end_date' => '2026-03-01',
            ]))
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors(['start_date', 'end_date']);

        $this->from(route('expenses.index'))
            ->get(route('expenses.index', ['start_date' => '2026-03-01']))
            ->assertRedirect(route('expenses.index'))
            ->assertSessionHasErrors('end_date');
    }

    private function createCompany(string $name): Company
    {
        return Company::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::uuid(),
        ]);
    }

    private function createAdmin(Company $company): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->company()->associate($company);
        $admin->save();

        return $admin;
    }
}
