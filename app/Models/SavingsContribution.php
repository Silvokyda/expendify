<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsContribution extends Model
{
    use HasFactory;

    // Columns: user_id, savings_goal_id, amount, contributed_at
    protected $fillable = ['user_id','savings_goal_id','amount','contributed_at'];

    protected $casts = [
        'amount'         => 'decimal:2',
        'contributed_at' => 'date',
    ];

    // Relations
    public function user() { return $this->belongsTo(User::class); }

    public function goal()
    {
        return $this->belongsTo(SavingsGoal::class, 'savings_goal_id');
    }

    // Scopes
    public function scopeForUser($q, int $userId) { return $q->where('user_id', $userId); }
    public function scopeForGoal($q, int $goalId) { return $q->where('savings_goal_id', $goalId); }

    public function scopeInMonth($q, int $year, int $month)
    {
        return $q->whereYear('contributed_at', $year)
                 ->whereMonth('contributed_at', $month);
    }
}
