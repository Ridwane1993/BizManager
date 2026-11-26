<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $expenseCategories = ExpenseCategory::query()
            ->withCount('expenses')
            ->orderBy('name')
            ->paginate(15);

        return view('expense-categories.index', compact('expenseCategories'));
    }

    public function create()
    {
        return view('expense-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')
                    ->where('company_id', $request->user()->company_id),
            ],
        ]);

        ExpenseCategory::create($validated);

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Catégorie de dépense créée avec succès.');
    }

    public function edit(ExpenseCategory $expense_category)
    {
        return view('expense-categories.edit', [
            'expenseCategory' => $expense_category,
        ]);
    }

    public function update(Request $request, ExpenseCategory $expense_category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')
                    ->where('company_id', $request->user()->company_id)
                    ->ignore($expense_category->getKey()),
            ],
        ]);

        DB::transaction(function () use ($expense_category, $validated): void {
            $expense_category->update($validated);
            $expense_category->expenses()->update([
                'category' => $validated['name'],
            ]);
        });

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Catégorie de dépense modifiée avec succès.');
    }

    public function destroy(ExpenseCategory $expense_category)
    {
        $expense_category->delete();

        return redirect()
            ->route('expense-categories.index')
            ->with('success', 'Catégorie de dépense supprimée. Les dépenses associées sont conservées.');
    }
}
