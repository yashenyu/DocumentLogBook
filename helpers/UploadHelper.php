<?php

class UploadHelper
{
    private static $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf'
    ];

    private static $maxSize = 5 * 1024 * 1024; // 5MB

    /**
     * Securely handles multiple file uploads
     * @param array $fileArray The $_FILES['input_name'] array
     * @return array Array of successful upload paths or throws exception
     */
    public static function handleUploads($fileArray)
    {
        $uploadedPaths = [];
        $fileCount = count($fileArray['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($fileArray['error'][$i] === UPLOAD_ERR_NO_FILE)
                continue;

            if ($fileArray['error'][$i] !== UPLOAD_ERR_OK) {
                throw new Exception("File upload error code: " . $fileArray['error'][$i]);
            }

            // 1. Size Validation
            if ($fileArray['size'][$i] > self::$maxSize) {
                throw new Exception("File is too large. Max limit is 5MB.");
            }

            // 2. MIME Type Validation (Server Side check of content)
            $tmpPath = $fileArray['tmp_name'][$i];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tmpPath);

            if (!array_key_exists($mimeType, self::$allowedMimeTypes)) {
                throw new Exception("Invalid file type. Only JPG, PNG, GIF, and PDF are allowed.");
            }

            // 3. Generate Secure Path (uploads/YYYY/MM/DD/hash.ext)
            $ext = self::$allowedMimeTypes[$mimeType];
            $subPath = date('Y/m/d');
            $uploadBase = 'uploads/' . $subPath . '/';

            if (!is_dir($uploadBase)) {
                mkdir($uploadBase, 0755, true);
            }

            $newName = bin2hex(random_bytes(16)) . '.' . $ext;
            $targetPath = $uploadBase . $newName;

            // 4. Move File
            if (move_uploaded_file($tmpPath, $targetPath)) {
                $uploadedPaths[] = $targetPath;
            }
            else {
                throw new Exception("Failed to move uploaded file.");
            }
        }

        return $uploadedPaths;
    }
}
