<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

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

    public function store(StoreExpenseCategoryRequest $request)
    {
        $validated = $request->validated();

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

    public function update(
        UpdateExpenseCategoryRequest $request,
        ExpenseCategory $expense_category
    ) {
        $validated = $request->validated();

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
