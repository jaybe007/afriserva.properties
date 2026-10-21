<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = ['super_admin'])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function handle(Request $request, Response $response): bool
    {
        if (!Auth::check()) {
            flash('warning', 'Please sign in to continue.');
            $response->redirect(url('login'));
            return false;
        }

        if (!Auth::hasRole($this->allowedRoles)) {
            if ($request->isAjax()) {
                $response->json(['error' => 'Unauthorized access for your role tier.'], 403);
                return false;
            }
            flash('danger', 'You do not have administrative clearance to access this module.');
            $response->redirect(url('dashboard'));
            return false;
        }

        return true;
    }
}
