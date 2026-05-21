<?php
/**
 * Rich Text Editor Image Upload API
 * Handles image uploads from Quill editor
 */

restrict_access();

// Start output buffering to prevent any output before JSON
ob_start();

header('Content-Type: application/json');

try {
    // Check if file was uploaded
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['image'];

    // Define allowed image types
    $allowed_types = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);

    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File is too large. Maximum size is 5MB.');
    }

    // Get file extension from mime type
    $extension_map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    $extension = $extension_map[$mime_type] ?? 'jpg';

    // Generate unique filename with timestamp
    $timestamp = date('Y-m-d');
    $unique_name = $timestamp . '_' . uniqid() . '.' . $extension;

    // Get session ID for organizing uploads (unique per editor instance)
    $session_id = $_POST['session_id'] ?? 'default';

    // Sanitize session_id to prevent directory traversal
    $session_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $session_id);

    // Save to session-specific folder (e.g., editor_images/abc123-xyz789/)
    $relative_path = 'editor_images/' . $session_id;
    $upload_dir = Path::uploads($relative_path);

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $destination = $upload_dir . '/' . $unique_name;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Build the public URL for the uploaded image
        $club_slug = ClubManagementService::getSelectedClubSlug();
        $url = "/.club_data/{$club_slug}/uploads/{$relative_path}/{$unique_name}";

        logger()->info("Editor image uploaded", [
            'user' => User::getCurrent()->login,
            'filename' => $unique_name,
            'session_id' => $session_id,
            'size' => $file['size']
        ]);

        // Clear any buffered output before sending JSON
        ob_end_clean();

        echo json_encode([
            'success' => true,
            'url' => $url,
            'filename' => $unique_name,
            'size' => $file['size']
        ]);
        exit;
    } else {
        throw new Exception('Failed to save file');
    }

} catch (Exception $e) {
    logger()->error("Editor image upload failed", [
        'error' => $e->getMessage(),
        'user' => User::getCurrent()->login ?? 'unknown'
    ]);

    // Clear any buffered output before sending JSON
    ob_end_clean();

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
