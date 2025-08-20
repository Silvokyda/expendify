<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Show the list of transactions (web).
     */
    public function index(Request $request)
    {
        $transactions = Transaction::forUser($request->user()->id)
            ->with('category')
            ->latest('occurred_at')
            ->paginate(15);

        return view('app.transactions.index', compact('transactions'));
    }

    /**
     * Show the create transaction form.
     */
    public function create(Request $request)
    {
        $categories = Category::forUser($request->user()->id)->get();
        return view('app.transactions.create', compact('categories'));
    }

    /**
     * Store a new transaction from the form.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'        => ['required', 'in:income,expense'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'note'        => ['nullable', 'string', 'max:500'],
            'occurred_at' => ['required', 'date'],
        ]);

        $transaction = Transaction::create([
            'user_id'     => $request->user()->id,
            'type'        => $data['type'],
            'amount'      => $data['amount'],
            'category_id' => $data['category_id'] ?? null,
            'note'        => $data['note'] ?? null,
            'occurred_at' => $data['occurred_at'],
        ]);

        return redirect()->route('transactions.index')
            ->with('status', 'Transaction added successfully.');
    }
}
