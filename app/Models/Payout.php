<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    // Columns: user_id, wallet_id, channel(TILL|PAYBILL|P2P), destination, amount, reference, status, meta
    protected $fillable = ['user_id','wallet_id','channel','destination','amount','reference','status','meta'];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta'   => 'array',
    ];

    public function user()   { return $this->belongsTo(User::class); }
    public function wallet() { return $this->belongsTo(Wallet::class); }
}
