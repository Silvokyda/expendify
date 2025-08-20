<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    // List budgets (expense categories)
    public function index(Request $request)
    {
        $categories = Category::query()
            ->forUser($request->user()->id)
            ->expense()
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'monthly_limit', 'created_at']);

        return view('app.budgets.index', compact('categories'));
    }

    public function create()
    {
        return view('app.budgets.create');
    }

    // Create budget (category + optional monthly_limit)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => [
                'required',
                'string',
                'max:64',
                Rule::unique('categories', 'name')->where(fn($q) => $q->where('user_id', $request->user()->id))
            ],
            'monthly_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        Category::create([
            'user_id'       => $request->user()->id,
            'name'          => $data['name'],
            'type'          => 'expense',
            'monthly_limit' => $data['monthly_limit'] ?? 0,
        ]);

        return redirect()->route('budgets.index')->with('status', 'Budget created.');
    }

    public function edit(Request $request, Category $budget)
    {
        $this->authorizeBudget($request, $budget);
        return view('app.budgets.edit', ['budget' => $budget]);
    }

    public function update(Request $request, Category $budget)
    {
        $this->authorizeBudget($request, $budget);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:64',
                Rule::unique('categories', 'name')
                    ->ignore($budget->id)
                    ->where(fn($q) => $q->where('user_id', $request->user()->id))
            ],
            'monthly_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $budget->update([
            'name'          => $data['name'],
            'monthly_limit' => $data['monthly_limit'] ?? 0,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'budget' => $budget]);
        }
        return redirect()->route('budgets.index')->with('status', 'Budget updated.');
    }

    public function destroy(Request $request, Category $budget)
    {
        $this->authorizeBudget($request, $budget);
        $budget->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return redirect()->route('budgets.index')->with('status', 'Budget deleted.');
    }


    private function authorizeBudget(Request $request, Category $category): void
    {
        abort_unless($category->user_id === $request->user()->id, 403);
        abort_unless($category->type === 'expense' || $category->type === 'both', 403);
    }
}
