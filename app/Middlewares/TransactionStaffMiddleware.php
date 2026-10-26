<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class TransactionStaffMiddleware
{
    private array $allowedRoles = ['super_admin', 'transaction_officer', 'finance_admin', 'moderator'];

    public function handle(Request $request, Response $response): bool
    {
        if (!Auth::check()) {
            flash('warning', 'Please sign in to access the Afriserva Transaction Desk.');
            $response->redirect(url('login'));
            return false;
        }

        if (!Auth::hasRole($this->allowedRoles)) {
            if ($request->isAjax()) {
                $response->json(['error' => 'Unauthorized clearance for Afriserva Transaction Desk.'], 403);
                return false;
            }
            flash('danger', 'You do not have staff authorization to access the Afriserva Transaction Desk.');
            $response->redirect(url('dashboard'));
            return false;
        }

        return true;
    }
}
