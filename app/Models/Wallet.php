<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    // Columns: user_id, msisdn, balance
    protected $fillable = ['user_id','msisdn','balance'];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
