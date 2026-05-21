<?php

/**
 * Rich Text Helper Functions
 * Utilities for working with rich text content
 */

class RichTextHelper
{
    /**
     * Sanitize and display rich text HTML content
     * 
     * @param string|null $html The HTML content to sanitize
     * @param bool $stripImages Remove images from output
     * @return string Safe HTML for display
     */
    public static function display(?string $html, bool $stripImages = false): string
    {
        if (empty($html)) {
            return '';
        }

        // Remove script tags (double safety)
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);

        // Remove dangerous attributes
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);

        // Optionally remove images
        if ($stripImages) {
            $html = preg_replace('/<img[^>]+>/i', '', $html);
        }

        // Add target="_blank" to links for security
        $html = preg_replace('/<a\s+([^>]*?)href=/i', '<a $1target="_blank" rel="noopener noreferrer" href=', $html);

        return $html;
    }

    /**
     * Extract plain text from rich text HTML
     * 
     * @param string|null $html The HTML content
     * @param int $maxLength Maximum length (0 = no limit)
     * @return string Plain text
     */
    public static function toPlainText(?string $html, int $maxLength = 0): string
    {
        if (empty($html)) {
            return '';
        }

        // Convert <br> and <p> to line breaks
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);

        // Strip all HTML tags
        $text = strip_tags($html);

        // Clean up whitespace
        $text = preg_replace('/\n\n+/', "\n\n", $text);
        $text = trim($text);

        // Truncate if needed
        if ($maxLength > 0 && mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength) . '...';
        }

        return $text;
    }

    /**
     * Create a preview/excerpt from rich text
     * 
     * @param string|null $html The HTML content
     * @param int $length Length of preview
     * @param bool $preserveFormatting Keep basic formatting
     * @return string Preview text
     */
    public static function preview(?string $html, int $length = 150, bool $preserveFormatting = false): string
    {
        if (empty($html)) {
            return '';
        }

        if ($preserveFormatting) {
            // Keep basic formatting tags
            $text = strip_tags($html, '<strong><em><b><i>');
            $text = self::toPlainText($text, $length);
        } else {
            $text = self::toPlainText($html, $length);
        }

        return $text;
    }

    /**
     * Check if content has embedded images
     * 
     * @param string|null $html The HTML content
     * @return bool True if images found
     */
    public static function hasImages(?string $html): bool
    {
        if (empty($html)) {
            return false;
        }

        return preg_match('/<img[^>]+>/i', $html) === 1;
    }

    /**
     * Extract image URLs from content
     * 
     * @param string|null $html The HTML content
     * @return array Array of image URLs
     */
    public static function extractImageUrls(?string $html): array
    {
        if (empty($html)) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches);

        return $matches[1] ?? [];
    }

    /**
     * Convert markdown to HTML (simple conversion)
     * Useful for migrating existing markdown content
     * 
     * @param string|null $markdown Markdown content
     * @return string HTML content
     */
    public static function markdownToHtml(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        // This is a simple conversion, for full markdown use a library like Parsedown
        $html = $markdown;

        // Headers
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);

        // Bold
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);

        // Italic
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html);

        // Links
        $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2" target="_blank">$1</a>', $html);

        // Line breaks to paragraphs
        $paragraphs = explode("\n\n", $html);
        $html = '<p>' . implode('</p><p>', $paragraphs) . '</p>';

        return $html;
    }

    /**
     * Estimate reading time for content
     * 
     * @param string|null $html The HTML content
     * @param int $wordsPerMinute Average reading speed
     * @return int Reading time in minutes
     */
    public static function estimateReadingTime(?string $html, int $wordsPerMinute = 200): int
    {
        if (empty($html)) {
            return 0;
        }

        $text = self::toPlainText($html);
        $wordCount = str_word_count($text);

        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Get word count from rich text
     * 
     * @param string|null $html The HTML content
     * @return int Word count
     */
    public static function wordCount(?string $html): int
    {
        if (empty($html)) {
            return 0;
        }

        $text = self::toPlainText($html);
        return str_word_count($text);
    }

    /**
     * Wrap content in a display container with proper styling
     * 
     * @param string|null $html The HTML content
     * @param string $class Additional CSS classes
     * @return string Wrapped HTML
     */
    public static function wrap(?string $html, string $class = ''): string
    {
        if (empty($html)) {
            return '';
        }

        $safeHtml = self::display($html);
        $classes = trim("richtext-content " . $class);

        return "<div class=\"{$classes}\">{$safeHtml}</div>";
    }
}

// Global helper function for convenience
if (!function_exists('richtext')) {
    /**
     * Display rich text content safely
     * 
     * @param string|null $html Rich text HTML content
     * @param bool $stripImages Remove images
     * @return string Safe HTML
     */
    function richtext(?string $html, bool $stripImages = false): string
    {
        return RichTextHelper::display($html, $stripImages);
    }
}

if (!function_exists('richtext_preview')) {
    /**
     * Create a preview from rich text
     * 
     * @param string|null $html Rich text HTML content
     * @param int $length Preview length
     * @return string Preview text
     */
    function richtext_preview(?string $html, int $length = 150): string
    {
        return RichTextHelper::preview($html, $length);
    }
}

if (!function_exists('richtext_plain')) {
    /**
     * Convert rich text to plain text
     * 
     * @param string|null $html Rich text HTML content
     * @param int $maxLength Maximum length
     * @return string Plain text
     */
    function richtext_plain(?string $html, int $maxLength = 0): string
    {
        return RichTextHelper::toPlainText($html, $maxLength);
    }
}
