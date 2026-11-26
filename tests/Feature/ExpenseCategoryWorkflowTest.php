<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExpenseCategoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_categories_without_losing_linked_expenses(): void
    {
        [$admin, $company] = $this->createAdmin('Expense category company');

        $this->actingAs($admin)
            ->get(route('expense-categories.index'))
            ->assertOk();
        $this->get(route('expense-categories.create'))->assertOk();

        $this->post(route('expense-categories.store'), [
            'name' => 'Transport',
        ])->assertRedirect(route('expense-categories.index'));

        $category = ExpenseCategory::firstOrFail();
        $this->assertSame((int) $company->getKey(), (int) $category->company_id);

        $expense = Expense::create([
            'category' => $category->name,
            'expense_category_id' => $category->getKey(),
            'amount' => 45.50,
            'expense_date' => now()->toDateString(),
            'description' => 'Déplacement professionnel',
        ]);

        $this->get(route('expense-categories.index'))
            ->assertOk()
            ->assertSee('Transport')
            ->assertSee('1');
        $this->get(route('expense-categories.edit', $category))->assertOk();

        $this->put(route('expense-categories.update', $category), [
            'name' => 'Frais de déplacement',
        ])->assertRedirect(route('expense-categories.index'));

        $expense->refresh();
        $this->assertSame('Frais de déplacement', $expense->category);
        $this->assertSame('Frais de déplacement', $expense->expenseCategory->name);

        $this->delete(route('expense-categories.destroy', $category))
            ->assertRedirect(route('expense-categories.index'));

        $expense->refresh();
        $this->assertDatabaseMissing('expense_categories', [
            'id' => $category->getKey(),
        ]);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->getKey(),
            'category' => 'Frais de déplacement',
            'expense_category_id' => null,
        ]);
        $this->assertNull($expense->expenseCategory);
    }

    public function test_category_names_are_unique_per_company_and_management_is_role_limited(): void
    {
        [$admin, $company] = $this->createAdmin('Company A');
        $this->actingAs($admin);

        $this->post(route('expense-categories.store'), [
            'name' => 'Utilities',
        ])->assertRedirect(route('expense-categories.index'));

        $this->from(route('expense-categories.create'))
            ->post(route('expense-categories.store'), [
                'name' => 'Utilities',
            ])
            ->assertRedirect(route('expense-categories.create'))
            ->assertSessionHasErrors('name');

        $otherCompany = Company::create([
            'name' => 'Company B',
            'slug' => 'company-b-'.Str::uuid(),
        ]);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $otherAdmin->company()->associate($otherCompany);
        $otherAdmin->save();

        $this->actingAs($otherAdmin)
            ->post(route('expense-categories.store'), [
                'name' => 'Utilities',
            ])
            ->assertRedirect(route('expense-categories.index'));

        $this->assertDatabaseCount('expense_categories', 2);
        $this->assertDatabaseHas('expense_categories', [
            'company_id' => $company->getKey(),
            'name' => 'Utilities',
        ]);
        $this->assertDatabaseHas('expense_categories', [
            'company_id' => $otherCompany->getKey(),
            'name' => 'Utilities',
        ]);

        $employee = User::factory()->create([
            'role' => 'employee',
            'company_id' => $company->getKey(),
        ]);
        $this->actingAs($employee)
            ->get(route('expense-categories.index'))
            ->assertForbidden();
    }

    private function createAdmin(string $name): array
    {
        $company = Company::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::uuid(),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->company()->associate($company);
        $admin->save();

        return [$admin, $company];
    }
}
