<?php

namespace App\Middlewares;

use App\Core\CSRF;
use App\Core\Request;
use App\Core\Response;

class CsrfMiddleware
{
    public function handle(Request $request, Response $response): bool
    {
        if ($request->isPost()) {
            $token = $request->input('_csrf_token') 
                ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
                ?? null;

            if (!CSRF::validate($token)) {
                if ($request->isAjax()) {
                    $response->json(['error' => 'Security token invalid or expired. Please refresh the page.'], 419);
                    return false;
                }
                flash('danger', 'Your session expired. Please resubmit the form.');
                $response->redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
                return false;
            }
        }
        return true;
    }
}
