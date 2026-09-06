<?php

namespace App\Actions\DataManagement;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteAccountAction
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            // Revoke all API tokens first.
            $user->tokens()->delete();

            // Soft delete the account.
            $user->delete();
        });
    }
}