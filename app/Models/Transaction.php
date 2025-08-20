<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use HasFactory;

    // Columns: user_id, category_id, type(income|expense), amount, occurred_at, note
    protected $fillable = [
        'user_id','category_id','type','amount','occurred_at','note',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'occurred_at' => 'date',
    ];

    // Relations
    public function user()     { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }

    // Scopes
    public function scopeForUser(Builder $q, int $userId): Builder
    { return $q->where('user_id', $userId); }

    public function scopeIncome(Builder $q): Builder
    { return $q->where('type', 'income'); }

    public function scopeExpense(Builder $q): Builder
    { return $q->where('type', 'expense'); }

    public function scopeBetweenDates(Builder $q, $start, $end): Builder
    { return $q->whereBetween('occurred_at', [$start, $end]); }

    public function scopeInMonth(Builder $q, int $year, int $month): Builder
    {
        return $q->whereYear('occurred_at', $year)
                 ->whereMonth('occurred_at', $month);
    }
}
