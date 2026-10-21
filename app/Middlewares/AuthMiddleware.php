<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class AuthMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        if (!Auth::check()) {
            if ($request->isAjax()) {
                $response->json(['error' => 'Unauthenticated session. Please log in.'], 401);
                return false;
            }
            flash('warning', 'Please sign in to access your portal or dashboard.');
            $response->redirect(url('login?redirect=' . urlencode($request->path())));
            return false;
        }
        return true;
    }
}
