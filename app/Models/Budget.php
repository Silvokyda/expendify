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
     * Computed view-only flag for UI: true when "now" falls inside the budget's current window.
     * NOTE: this is intentionally NOT overriding the persisted is_active column.
     */
    public function getCurrentlyActiveAttribute(): bool
    {
        [$start, $end] = $this->currentWindow();
        if (!$start || !$end)
            return false;

        $now = Carbon::now();
        return $now->between($start, $end);
    }

    /**
     * Returns [startCarbon, endCarbon] for the present cycle of this budget.
     * - monthly: current calendar month (local)
     * - weekly : Monday..Sunday (explicit)
     * - custom : bounded by start_date..end_date
     */
    public function currentWindow(): array
    {
        if ($this->period === 'monthly') {
            return [now()->copy()->startOfMonth(), now()->copy()->endOfMonth()];
        }
        if ($this->period === 'weekly') {
            return [now()->copy()->startOfWeek(Carbon::MONDAY), now()->copy()->endOfWeek(Carbon::SUNDAY)];
        }
        $s = $this->start_date ? Carbon::parse($this->start_date)->startOfDay() : null;
        $e = $this->end_date ? Carbon::parse($this->end_date)->endOfDay() : null;
        return [$s, $e];
    }
}
