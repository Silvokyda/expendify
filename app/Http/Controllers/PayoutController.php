<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'channel'     => ['required','in:TILL,PAYBILL,P2P'],
            'destination' => ['required','string','max:64'],
            'amount'      => ['required','numeric','min:1'],
            'reference'   => ['nullable','string','max:64'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        // MVP: check simple balance
        if ($wallet->balance < $data['amount']) {
            return response()->json(['message' => 'Insufficient wallet balance.'], 422);
        }

        $payout = Payout::create([
            'user_id'     => $user->id,
            'wallet_id'   => $wallet->id,
            'channel'     => $data['channel'],
            'destination' => $data['destination'],
            'amount'      => $data['amount'],
            'reference'   => $data['reference'] ?? null,
            'status'      => 'PENDING',
        ]);

        // TODO: hand off to job to call gateway (Daraja/PSP)
        // dispatch(new ProcessPayout($payout));

        return response()->json(['payout' => $payout], 201);
    }
}
