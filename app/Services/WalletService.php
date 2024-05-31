<?php

namespace App\Services;

use App\Models\User;

class WalletService extends Service
{
    public static function chargeWallet(User $user, $amount)
    {
        $userWallet = $user->wallet;
        $userWallet->balance += $amount;
        $userWallet->save();
        return $userWallet;
    }
}