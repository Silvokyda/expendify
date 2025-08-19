<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsContributionController extends BaseApiController
{
    public function index(Request $request)
    {
        $q = SavingsContribution::where('user_id', $request->user()->id)
            ->when($request->filled('savings_goal_id'), fn($qq) => $qq->where('savings_goal_id', $request->savings_goal_id))
            ->orderByDesc('date');

        $paginator = $q->paginate((int)$request->get('per_page', 15));
        return $this->paginateResponse($paginator, fn($c) => $this->transform($c));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'savings_goal_id' => ['required','exists:savings_goals,id'],
            'amount'          => ['required','numeric','min:0'],
            'date'            => ['required','date'],
            'note'            => ['nullable','string','max:500'],
        ]);

        $goal = SavingsGoal::findOrFail($data['savings_goal_id']);
        if ($goal->user_id !== $request->user()->id) return $this->forbidden('Goal not owned');

        $contrib = SavingsContribution::create($data + ['user_id' => $request->user()->id]);

        return $this->created($this->transform($contrib), 'Contribution added');
    }

    public function show(Request $request, SavingsContribution $savings_contribution)
    {
        if ($savings_contribution->user_id !== $request->user()->id) return $this->forbidden();
        return $this->success($this->transform($savings_contribution));
    }

    public function update(Request $request, SavingsContribution $savings_contribution)
    {
        if ($savings_contribution->user_id !== $request->user()->id) return $this->forbidden();

        $data = $request->validate([
            'amount' => ['sometimes','numeric','min:0'],
            'date'   => ['sometimes','date'],
            'note'   => ['sometimes','nullable','string','max:500'],
        ]);

        $savings_contribution->update($data);
        return $this->success($this->transform($savings_contribution), 'Contribution updated');
    }

    public function destroy(Request $request, SavingsContribution $savings_contribution)
    {
        if ($savings_contribution->user_id !== $request->user()->id) return $this->forbidden();
        $savings_contribution->delete();
        return $this->success(null, 'Contribution deleted');
    }

    private function transform(SavingsContribution $c): array
    {
        return [
            'id'              => $c->id,
            'savings_goal_id' => $c->savings_goal_id,
            'amount'          => (float)$c->amount,
            'date'            => optional($c->date)->toDateString(),
            'note'            => $c->note,
            'created_at'      => optional($c->created_at)->toIso8601String(),
        ];
    }
}
