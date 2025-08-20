<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'msisdn' => ['required','string','max:20'],
        ]);

        $user = $request->user();

        abort_if($user->has_wallet, 422, 'Wallet already exists.');

        $wallet = DB::transaction(function () use ($user, $data) {
            $w = \App\Models\Wallet::create([
                'user_id' => $user->id,
                'msisdn'  => $data['msisdn'],
                'balance' => 0,
            ]);
            $user->forceFill(['has_wallet' => true])->save();

            return $w;
        });

        return response()->json(['wallet' => $wallet], 201);
    }
}

