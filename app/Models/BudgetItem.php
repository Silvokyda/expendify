<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id',
        'category_id',
        'savings_goal_id',
        'type',
        'amount',
        'note'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function savingsGoal()
    {
        return $this->belongsTo(SavingsGoal::class);
    }
}
