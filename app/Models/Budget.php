<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Budget extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'period',
        'total_amount',
        'start_date',
        'end_date',
        'is_active',
        'activated_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }
    public function incomes()
    {
        return $this->items()->where('type', 'income');
    }
    public function expenses()
    {
        return $this->items()->where('type', 'expense');
    }
    public function savings()
    {
        return $this->items()->where('type', 'saving');
    }

    /**
     * Dynamic view of activity: a budget is active when "now" is inside its current period window.
     * - monthly: current calendar month
     * - weekly: current calendar week
     * - custom : between start_date and end_date (inclusive)
     */
    public function getIsActiveAttribute($value): bool
    {
        [$start, $end] = $this->currentWindow();
        if (!$start || !$end)
            return false;
        $now = Carbon::now();
        return $now->between($start, $end);
    }

    /**
     * Returns [startCarbon, endCarbon] for the present cycle of this budget.
     */
    public function currentWindow(): array
    {
        if ($this->period === 'monthly') {
            return [now()->copy()->startOfMonth(), now()->copy()->endOfMonth()];
        }
        if ($this->period === 'weekly') {
            return [now()->copy()->startOfWeek(), now()->copy()->endOfWeek()];
        }
        $s = $this->start_date ? Carbon::parse($this->start_date)->startOfDay() : null;
        $e = $this->end_date ? Carbon::parse($this->end_date)->endOfDay() : null;
        return [$s, $e];
    }
}
