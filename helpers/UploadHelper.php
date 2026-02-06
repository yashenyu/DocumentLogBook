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
     * Validates and reads multiple file uploads
     * @param array $fileArray The $_FILES['input_name'] array
     * @return array Array of ['data' => binary, 'type' => mime_type] for each valid file
     */
    public static function handleUploads($fileArray)
    {
        $results = [];
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

            // 3. Read file content as binary
            $fileData = file_get_contents($tmpPath);
            if ($fileData === false) {
                throw new Exception("Failed to read uploaded file.");
            }

            $results[] = [
                'data' => $fileData,
                'type' => $mimeType
            ];
        }

        return $results;
    }
}
