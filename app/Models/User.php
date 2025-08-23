<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property bool $has_wallet
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_wallet' => 'boolean',
        ];
    }

    // Relations
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
    public function categoryBudgets()
    {
        return $this->hasMany(\App\Models\CategoryBudget::class);
    }
    public function savingsGoals()
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function budgetItems()
    {
        return $this->hasManyThrough(BudgetItem::class, Budget::class);
    }
    
}
