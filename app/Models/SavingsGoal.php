<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsGoal extends Model
{
    use HasFactory;

    // Columns: user_id, name, target_amount, monthly_target, start_date, end_date
    protected $fillable = [
        'user_id','name','target_amount','monthly_target','start_date','end_date',
    ];

    protected $casts = [
        'target_amount'  => 'decimal:2',
        'monthly_target' => 'decimal:2',
        'start_date'     => 'date',
        'end_date'       => 'date',
    ];

    // Relations
    public function user()           { return $this->belongsTo(User::class); }
    public function contributions()  { return $this->hasMany(SavingsContribution::class); }

    // Accessors
    public function getCurrentAmountAttribute(): float
    {
        return (float) ($this->relationLoaded('contributions')
            ? $this->contributions->sum('amount')
            : $this->contributions()->sum('amount'));
    }

    public function getProgressPercentAttribute(): float
    {
        $target = (float) ($this->target_amount ?? 0);
        return $target > 0 ? min(100, round(($this->current_amount / $target) * 100, 2)) : 0.0;
    }

    // Scopes
    public function scopeForUser($q, int $userId) { return $q->where('user_id', $userId); }
}
