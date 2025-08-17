<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'monthly_target',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'monthly_target' => 'decimal:2',
        'start_date'     => 'date',
        'end_date'       => 'date',
    ];

    /* -----------------------
     | Relations
     * ---------------------*/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contributions()
    {
        return $this->hasMany(SavingsContribution::class);
    }

    /* -----------------------
     | Accessors
     * ---------------------*/
    public function getCurrentAmountAttribute(): float
    {
        // Sum on a loaded relationship if available (prevents N+1), else query.
        if ($this->relationLoaded('contributions')) {
            return (float) $this->contributions->sum('amount');
        }
        return (float) $this->contributions()->sum('amount');
    }

    public function getProgressPercentAttribute(): float
    {
        $target = (float) ($this->target_amount ?? 0);
        if ($target <= 0) return 0.0;
        return min(100, round(($this->current_amount / $target) * 100, 2));
    }

    /* -----------------------
     | Scopes
     * ---------------------*/
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
