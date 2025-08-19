<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends BaseApiController
{
    public function index(Request $request)
    {
        $q = Transaction::where('user_id', $request->user()->id)
            ->when($request->filled('type'), fn($qq) => $qq->where('type', $request->type)) // income|expense
            ->when($request->filled('category_id'), fn($qq) => $qq->where('category_id', $request->category_id))
            ->when($request->filled('from'), fn($qq) => $qq->whereDate('date', '>=', $request->from))
            ->when($request->filled('to'), fn($qq) => $qq->whereDate('date', '<=', $request->to))
            ->latest();

        $paginator = $q->paginate((int)$request->get('per_page', 15));
        return $this->paginateResponse($paginator, fn($t) => $this->transform($t));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'        => ['required','in:income,expense'],
            'amount'      => ['required','numeric','min:0'],
            'category_id' => ['nullable','exists:categories,id'],
            'note'        => ['nullable','string','max:500'],
            'date'        => ['required','date'],
        ]);

        $tx = Transaction::create($data + ['user_id' => $request->user()->id]);
        return $this->created($this->transform($tx), 'Transaction created');
    }

    public function show(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return $this->forbidden();
        }
        return $this->success($this->transform($transaction));
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'type'        => ['sometimes','in:income,expense'],
            'amount'      => ['sometimes','numeric','min:0'],
            'category_id' => ['sometimes','nullable','exists:categories,id'],
            'note'        => ['sometimes','nullable','string','max:500'],
            'date'        => ['sometimes','date'],
        ]);

        $transaction->update($data);
        return $this->success($this->transform($transaction), 'Transaction updated');
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return $this->forbidden();
        }
        $transaction->delete();
        return $this->success(null, 'Transaction deleted');
    }

    public function monthlySummary(Request $request)
    {
        $userId = $request->user()->id;
        $year   = (int)($request->query('year', now()->year));
        $month  = (int)($request->query('month', now()->month));

        $totals = Transaction::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw("
                SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expenses
            ")->first();

        return $this->success([
            'year'     => $year,
            'month'    => $month,
            'income'   => (float)($totals->income ?? 0),
            'expenses' => (float)($totals->expenses ?? 0),
            'net'      => (float)(($totals->income ?? 0) - ($totals->expenses ?? 0)),
        ], 'Monthly summary');
    }

    public function yearlySummary(Request $request)
    {
        $userId = $request->user()->id;
        $year   = (int)($request->query('year', now()->year));

        $rows = Transaction::where('user_id', $userId)
            ->whereYear('date', $year)
            ->selectRaw("
                MONTH(date) as month,
                SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income,
                SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expenses
            ")
            ->groupBy('month')->orderBy('month')->get();

        return $this->success($rows, 'Yearly summary');
    }

    private function transform(Transaction $t): array
    {
        return [
            'id'          => $t->id,
            'type'        => $t->type,
            'amount'      => (float)$t->amount,
            'category_id' => $t->category_id,
            'note'        => $t->note,
            'date'        => optional($t->date)->toDateString(),
            'created_at'  => optional($t->created_at)->toIso8601String(),
        ];
    }
}
