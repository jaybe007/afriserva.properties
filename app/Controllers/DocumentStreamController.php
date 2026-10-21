<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;

class DocumentStreamController extends Controller
{
    public function download(Request $request, Response $response, array $params): void
    {
        if (!Auth::check()) {
            flash('warning', 'Please sign in to inspect verified legal documents.');
            $this->redirect($response, url('login'));
            return;
        }

        $docId = (int)($params['id'] ?? 0);
        $doc = Database::fetch("SELECT * FROM property_documents WHERE id = ?", [$docId]);

        if (!$doc) {
            $response->status(404)->html("<h3>Document record not found.</h3>", 404);
            return;
        }

        $currentUserId = Auth::id();
        $isStaff = Auth::hasRole(['super_admin', 'moderator', 'finance_admin']);
        $isOwner = ((int)$doc['user_id'] === $currentUserId);

        if (!$isStaff && !$isOwner) {
            $response->status(403)->html("<h3>Unauthorized: Only compliance officers and the listing party may inspect private title deeds.</h3>", 403);
            return;
        }

        $filePath = $doc['file_path'];
        // Check local storage
        if (!file_exists($filePath)) {
            // Check storage/documents/
            $altPath = __DIR__ . '/../../storage/documents/' . basename($filePath);
            if (file_exists($altPath)) {
                $filePath = $altPath;
            } else {
                $response->status(404)->html("<h3>Physical document file not found on secure storage.</h3>", 404);
                return;
            }
        }

        // Stream authenticated document binary
        $fileName = basename($doc['file_name']);
        $mime = mime_content_type($filePath) ?: 'application/octet-stream';

        header("Content-Type: {$mime}");
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header("Content-Length: " . filesize($filePath));
        header("Cache-Control: private, no-transform, no-store, must-revalidate");
        header("X-Content-Type-Options: nosniff");
        readfile($filePath);
        exit;
    }
}
