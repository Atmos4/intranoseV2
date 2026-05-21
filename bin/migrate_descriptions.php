/**
* Migration Script - Convert Markdown Descriptions to Rich Text HTML
*
* This script helps convert existing markdown event descriptions to HTML
* for the new rich text editor.
*
* USAGE:
* php bin/migrate_descriptions.php
*/

<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../engine/setup.php';

// Get all clubs
$clubs = ClubManagementService::create()->listClubs();

foreach ($clubs as $club) {
    echo "Processing club: {$club->name} ({$club->slug})\n";

    DB::setupForClub($club->slug);
    $em = em();

    // Get all events
    $events = $em->getRepository(Event::class)->findAll();
    $converted = 0;

    foreach ($events as $event) {
        if ($event->description && !empty($event->description)) {
            // Check if it's already HTML (contains HTML tags)
            if (preg_match('/<[^>]+>/', $event->description)) {
                echo "  - Event #{$event->id}: Already HTML, skipping\n";
                continue;
            }

            // Convert Markdown to HTML using RichTextHelper
            $htmlDescription = RichTextHelper::markdownToHtml($event->description);
            $event->description = $htmlDescription;
            $converted++;

            echo "  - Event #{$event->id}: Converted from markdown\n";
        }

        // Process activities
        foreach ($event->activities as $activity) {
            if ($activity->description && !empty($activity->description)) {
                if (preg_match('/<[^>]+>/', $activity->description)) {
                    echo "    - Activity #{$activity->id}: Already HTML, skipping\n";
                    continue;
                }

                $htmlDescription = RichTextHelper::markdownToHtml($activity->description);
                $activity->description = $htmlDescription;
                $converted++;

                echo "    - Activity #{$activity->id}: Converted from markdown\n";
            }
        }
    }

    if ($converted > 0) {
        $em->flush();
        echo "  ✓ Converted {$converted} descriptions\n";
    } else {
        echo "  - No descriptions to convert\n";
    }

    echo "\n";
}

echo "Migration complete!\n";
