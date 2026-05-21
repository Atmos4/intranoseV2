<?php
/**
 * Rich Text Editor Image Cleanup API
 * Removes orphaned images that were uploaded but not used in final content
 */

restrict_access();

// Start output buffering to prevent any output before JSON
ob_start();

header('Content-Type: application/json');

try {
    // Get input parameters
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Invalid JSON input');
    }

    $session_id = $input['session_id'] ?? null;
    $referenced_urls = $input['referenced_urls'] ?? [];

    if (!$session_id) {
        throw new Exception('session_id is required');
    }

    // Sanitize session_id to prevent directory traversal
    $session_id = preg_replace('/[^a-zA-Z0-9_-]/', '', $session_id);

    if (!is_array($referenced_urls)) {
        throw new Exception('referenced_urls must be an array');
    }

    // Build the directory path
    $relative_path = 'editor_images/' . $session_id;
    $upload_dir = Path::uploads($relative_path);

    if (!is_dir($upload_dir)) {
        // Directory doesn't exist, nothing to clean
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'deleted_count' => 0,
            'message' => 'No images directory found'
        ]);
        exit;
    }

    // Get club slug for URL matching
    $club_slug = ClubManagementService::getSelectedClubSlug();

    // Extract just the filenames from the referenced URLs
    $referenced_filenames = [];
    foreach ($referenced_urls as $url) {
        // Extract filename from URL like /.club_data/club/uploads/editor_images/event/123/image.jpg
        if (preg_match('/\/([^\/]+)$/', $url, $matches)) {
            $referenced_filenames[] = $matches[1];
        }
    }

    // Scan the directory for all images
    $files = scandir($upload_dir);
    $deleted_count = 0;
    $deleted_files = [];

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $file_path = $upload_dir . '/' . $file;

        // Check if it's a file
        if (!is_file($file_path)) {
            continue;
        }

        // Check if this file is referenced
        if (!in_array($file, $referenced_filenames)) {
            // File is not referenced, delete it
            if (unlink($file_path)) {
                $deleted_count++;
                $deleted_files[] = $file;
            }
        }
    }

    // Try to remove the directory if it's empty
    $remaining_files = array_diff(scandir($upload_dir), ['.', '..']);
    if (empty($remaining_files)) {
        rmdir($upload_dir);
    }

    logger()->info("Editor images cleaned up", [
        'user' => User::getCurrent()->login,
        'session_id' => $session_id,
        'deleted_count' => $deleted_count,
        'deleted_files' => $deleted_files
    ]);

    ob_end_clean();

    echo json_encode([
        'success' => true,
        'deleted_count' => $deleted_count,
        'deleted_files' => $deleted_files
    ]);
    exit;

} catch (Exception $e) {
    logger()->error("Editor image cleanup failed", [
        'error' => $e->getMessage(),
        'user' => User::getCurrent()->login ?? 'unknown'
    ]);

    ob_end_clean();

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
