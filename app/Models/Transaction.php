<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'type',        // income|expense
        'amount',
        'occurred_at',
        'note',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'occurred_at' => 'date',
    ];

    /* -----------------------
     | Relations
     * ---------------------*/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /* -----------------------
     | Scopes
     * ---------------------*/
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeBetweenDates(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('occurred_at', [$start, $end]);
    }

    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('occurred_at', $year)
                     ->whereMonth('occurred_at', $month);
    }
}
