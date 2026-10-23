<?php

namespace App\Helpers;

class UploadHelper
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/webp',
        'image/gif'
    ];
    private const MAX_FILE_SIZE = 12582912; // 12 MB

    /**
     * Upload an individual image file securely.
     *
     * @param array $file Single $_FILES item (e.g. $_FILES['cover_image_file'])
     * @param string $subdirectory Subfolder inside public/uploads/ (e.g. 'properties')
     * @return string|null Web-accessible relative URL (e.g. 'uploads/properties/filename.jpg') or null on failure
     */
    public static function uploadImage(array $file, string $subdirectory = 'properties'): ?string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return null;
        }

        // Validate extension
        $origName = $file['name'] ?? '';
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            return null;
        }

        // Check for double extension attack (e.g. shell.php.jpg)
        if (preg_match('/\.(php|phtml|php3|php4|php5|phps|cgi|pl|exe|sh|bat|js)\./i', $origName)) {
            return null;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return null;
        }

        // Target directory setup
        $baseUploadDir = dirname(__DIR__, 2) . '/public/uploads/' . trim($subdirectory, '/');
        if (!is_dir($baseUploadDir)) {
            mkdir($baseUploadDir, 0755, true);
        }

        // Generate cryptographically unique safe filename
        $safeName = 'prop_' . bin2hex(random_bytes(10)) . '_' . time() . '.' . $ext;
        $targetPath = $baseUploadDir . '/' . $safeName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $rel = 'uploads/' . trim($subdirectory, '/') . '/' . $safeName;
            return function_exists('url') ? url($rel) : '/' . $rel;
        }

        return null;
    }

    /**
     * Upload multiple image files securely (from a multi-file input: name="gallery_images[]").
     *
     * @param array $files Multi-file $_FILES item
     * @param string $subdirectory
     * @return array Array of relative URLs
     */
    public static function uploadMultipleImages(array $files, string $subdirectory = 'properties'): array
    {
        $uploadedUrls = [];
        if (empty($files['name']) || !is_array($files['name'])) {
            return $uploadedUrls;
        }

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if (empty($files['tmp_name'][$i]) || ($files['error'][$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                continue;
            }

            $singleFile = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i] ?? 0
            ];

            $url = self::uploadImage($singleFile, $subdirectory);
            if ($url) {
                $uploadedUrls[] = $url;
            }
        }

        return $uploadedUrls;
    }
}
