<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','name','type','monthly_limit'];

    // Relations
    public function user()         { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }

    // Scopes
    public function scopeForUser($q, int $userId)
    {
        // User-specific + global (null user_id) categories
        return $q->where(function ($qq) use ($userId) {
            $qq->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }

    public function scopeIncome($q)  { return $q->whereIn('type', ['income','both']); }
    public function scopeExpense($q) { return $q->whereIn('type', ['expense','both']); }
    public function budgets() { return $this->hasMany(\App\Models\CategoryBudget::class); }
}
