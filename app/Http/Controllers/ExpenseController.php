<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Afficher la liste des dépenses.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('expense_categories', 'id')
                    ->where('company_id', $request->user()->company_id),
            ],
            'start_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:end_date',
                'before_or_equal:end_date',
            ],
            'end_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_with:start_date',
                'after_or_equal:start_date',
            ],
        ]);

        $query = Expense::query();
        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($validated['category_id'])) {
            $query->where('expense_category_id', $validated['category_id']);
        }

        if (isset($validated['start_date'])) {
            $query->whereDate('expense_date', '>=', $validated['start_date']);
        }

        if (isset($validated['end_date'])) {
            $query->whereDate('expense_date', '<=', $validated['end_date']);
        }

        $expenses = $query
            ->latest('expense_date')
            ->paginate(15)
            ->withQueryString();
        $expenseCategories = ExpenseCategory::orderBy('name')->get();
        $filters = [
            'search' => $search,
            'category_id' => $validated['category_id'] ?? '',
            'start_date' => $validated['start_date'] ?? '',
            'end_date' => $validated['end_date'] ?? '',
        ];

        return view(
            'expenses.index',
            compact('expenses', 'expenseCategories', 'filters')
        );
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view('expenses.create', compact('expenseCategories'));
    }

    /**
     * Enregistrer une nouvelle dépense.
     */
    public function store(SaveExpenseRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $category = ExpenseCategory::firstOrCreate([
                'name' => $validated['category'],
            ]);

            Expense::create([
                ...$validated,
                'expense_category_id' => $category->getKey(),
            ]);
        });

        return redirect()
            ->route('expenses.index')
            ->with(
                'success',
                'Dépense enregistrée avec succès.'
            );
    }

    /**
     * Afficher une dépense.
     */
    public function show(Expense $expense)
    {
        return view(
            'expenses.show',
            compact('expense')
        );
    }

    /**
     * Formulaire de modification.
     */
    public function edit(Expense $expense)
    {
        $expenseCategories = ExpenseCategory::orderBy('name')->get();

        return view(
            'expenses.edit',
            compact('expense', 'expenseCategories')
        );
    }

    /**
     * Modifier une dépense.
     */
    public function update(
        SaveExpenseRequest $request,
        Expense $expense
    ) {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $expense): void {
            $category = ExpenseCategory::firstOrCreate([
                'name' => $validated['category'],
            ]);

            $expense->update([
                ...$validated,
                'expense_category_id' => $category->getKey(),
            ]);
        });

        return redirect()
            ->route('expenses.index')
            ->with(
                'success',
                'Dépense modifiée avec succès.'
            );
    }

    /**
     * Supprimer une dépense.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with(
                'success',
                'Dépense supprimée avec succès.'
            );
    }
}
