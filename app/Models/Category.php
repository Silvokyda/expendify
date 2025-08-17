<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'type']; 

    /* -----------------------
     | Relations
     * ---------------------*/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /* -----------------------
     | Scopes
     * ---------------------*/
    public function scopeForUser($query, $userId)
    {
        // User-specific + global (null user_id) categories
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }

    public function scopeIncome($query)
    {
        return $query->whereIn('type', ['income', 'both']);
    }

    public function scopeExpense($query)
    {
        return $query->whereIn('type', ['expense', 'both']);
    }
}
