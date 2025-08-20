<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryBudget extends Model
{
    protected $fillable = [
        'user_id','category_id','period','amount','start_date','end_date','is_active',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }

    /** Filter budgets overlapping a given range */
    public function scopeOverlapping($q, $start, $end)
    {
        return $q->where(function ($qq) use ($start, $end) {
            $qq->whereNull('start_date')->orWhereDate('start_date', '<=', $end);
        })->where(function ($qq) use ($start) {
            $qq->whereNull('end_date')->orWhereDate('end_date', '>=', $start);
        });
    }
}
