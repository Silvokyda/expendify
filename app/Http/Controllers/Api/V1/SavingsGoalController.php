<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends BaseApiController
{
    public function index(Request $request)
    {
        $items = SavingsGoal::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($g) => $this->transform($g));

        return $this->success($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required','string','max:120'],
            'target_amount'  => ['required','numeric','min:0'],
            'monthly_target' => ['nullable','numeric','min:0'],
            'due_date'       => ['nullable','date'],
        ]);

        $goal = SavingsGoal::create($data + ['user_id' => $request->user()->id]);

        return $this->created($this->transform($goal), 'Savings goal created');
    }

    public function show(Request $request, SavingsGoal $savings_goal)
    {
        if ($savings_goal->user_id !== $request->user()->id) return $this->forbidden();
        return $this->success($this->transform($savings_goal));
    }

    public function update(Request $request, SavingsGoal $savings_goal)
    {
        if ($savings_goal->user_id !== $request->user()->id) return $this->forbidden();

        $data = $request->validate([
            'name'           => ['sometimes','string','max:120'],
            'target_amount'  => ['sometimes','numeric','min:0'],
            'monthly_target' => ['sometimes','nullable','numeric','min:0'],
            'due_date'       => ['sometimes','nullable','date'],
        ]);

        $savings_goal->update($data);
        return $this->success($this->transform($savings_goal), 'Savings goal updated');
    }

    public function destroy(Request $request, SavingsGoal $savings_goal)
    {
        if ($savings_goal->user_id !== $request->user()->id) return $this->forbidden();
        $savings_goal->delete();
        return $this->success(null, 'Savings goal deleted');
    }

    private function transform(SavingsGoal $g): array
    {
        return [
            'id'              => $g->id,
            'name'            => $g->name,
            'target_amount'   => (float)$g->target_amount,
            'monthly_target'  => $g->monthly_target !== null ? (float)$g->monthly_target : null,
            'current_amount'  => (float)($g->current_amount ?? 0), // if you maintain a computed column
            'due_date'        => optional($g->due_date)->toDateString(),
            'created_at'      => optional($g->created_at)->toIso8601String(),
        ];
    }
}
