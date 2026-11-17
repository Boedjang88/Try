<?php

namespace App\Policies;

use App\Models\StudentInvoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the invoice or receipt.
     */
    public function view(User $user, StudentInvoice $invoice): bool
    {
        if (in_array($user->role, ['superadmin', 'bauk', 'baak', 'rector'])) {
            return true;
        }

        if ($user->role === 'student' && $user->student) {
            return $user->student->id === $invoice->student_id;
        }

        return false;
    }

    /**
     * Determine whether the user can verify payment of the invoice.
     */
    public function verify(User $user, StudentInvoice $invoice): bool
    {
        return in_array($user->role, ['superadmin', 'bauk']);
    }
}
