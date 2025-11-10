<?php

namespace App\Helpers;

use App\Models\LiveMutation;

class LiveMutationHelper
{
    /**
     * Update credit or debit of a live mutation and recalculate balance.
     *
     * @param  float|int  $amount
     * @param  string  $type  "debit" or "credit"
     * @return LiveMutation
     *
     * @throws \Exception
     */
    public static function updateLiveMutation(string $userBankCode, $amount, string $type)
    {
        $liveMutation = LiveMutation::where('userBankCode', $userBankCode)->first();

        if (! $liveMutation) {
            throw new \Exception("LiveMutation with user bank code {$userBankCode} not found.");
        }

        if ($type === 'credit') {
            $liveMutation->credit += $amount;
        } elseif ($type === 'debit') {
            $liveMutation->debit += $amount;
        } else {
            throw new \InvalidArgumentException("Invalid mutation type '{$type}'. Use 'credit' or 'debit'.");
        }

        $liveMutation->balance = $liveMutation->debit - $liveMutation->credit;
        $liveMutation->save();

        return $liveMutation;
    }
}
