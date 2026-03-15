<?php
/**
 * Rich Text Editor Demo Page
 * Demonstrates various Quill editor configurations
 */

restrict_access();

// Example 1: Full-featured editor
$v1 = new Validator(action: "example_full");
$content_full = $v1->richtext("content_full")
    ->label("Full Featured Editor")
    ->placeholder("Try all the features: formatting, lists, links, images...")
    ->required();

if ($v1->valid()) {
    Toast::success("Content saved! Check the HTML output.");
    logger()->info("Rich text content saved", [
        'user' => User::getCurrent()->login,
        'length' => strlen($content_full->value),
        'content_preview' => substr(strip_tags($content_full->value), 0, 100)
    ]);
}

// Example 2: Simple editor (no images, no code)
$v2 = new Validator(action: "example_simple");
$content_simple = $v2->richtext("content_simple")
    ->label("Simple Editor (No Images)")
    ->enable_images(false)
    ->enable_code(false)
    ->min_height(150)
    ->placeholder("A simpler editor for basic formatting...");

if ($v2->valid()) {
    Toast::success("Simple content saved!");
}

// Example 3: Minimal editor with custom toolbar
$v3 = new Validator(action: "example_minimal");
$content_minimal = $v3->richtext("content_minimal")
    ->label("Minimal Editor (Custom Toolbar)")
    ->toolbar([
        ['bold', 'italic'],
        [['list' => 'bullet']],
        ['clean']
    ])
    ->min_height(100)
    ->placeholder("Minimal formatting only...");

if ($v3->valid()) {
    Toast::success("Minimal content saved!");
}

// Example 4: Text-only (links but no images)
$v4 = new Validator(action: "example_text");
$content_text = $v4->richtext("content_text")
    ->label("Text & Links Only")
    ->enable_images(false)
    ->enable_links(true)
    ->enable_code(true)
    ->placeholder("Perfect for text-based content with links and code snippets...");

if ($v4->valid()) {
    Toast::success("Text content saved!");
}

// Example 5: Session ID for image organization (NEW!)
$v5 = new Validator(action: "example_session");
$content_session = $v5->richtext("content_session")
    ->label("Session-Based Image Upload")
    ->enable_images(true)
    ->session_id("demo-session-" . uniqid())
    ->placeholder("Upload images - they will be organized by session ID...");

if ($v5->valid()) {
    Toast::success("Content saved! Check cleanup example below.");
}

page("Rich Text Editor - Examples")->enableHelp();
?>

<style>
    .code-block {
        background: var(--pico-code-background-color);
        padding: 1rem;
        border-radius: var(--pico-border-radius);
        margin: 1rem 0;
        overflow-x: auto;
    }

    .code-block code {
        color: var(--pico-code-color);
        font-family: var(--pico-font-family-monospace);
        font-size: 0.875rem;
    }

    .feature-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }

    .feature-item {
        padding: 0.75rem;
        background: var(--pico-background-color);
        border-radius: var(--pico-border-radius);
        border-left: 3px solid var(--pico-primary);
    }
</style>

<?= actions()->back("/dev") ?>

<article>
    <h3><i class="fa fa-info-circle"></i> Rich Text Editor Features</h3>
    <div class="feature-list">
        <div class="feature-item">
            <strong><i class="fa fa-image"></i> Image Upload</strong><br>
            <small>Drag & drop, paste, or click to upload</small>
        </div>
        <div class="feature-item">
            <strong><i class="fa fa-bold"></i> Text Formatting</strong><br>
            <small>Bold, italic, underline, strikethrough</small>
        </div>
        <div class="feature-item">
            <strong><i class="fa fa-list"></i> Lists</strong><br>
            <small>Ordered and unordered lists</small>
        </div>
        <div class="feature-item">
            <strong><i class="fa fa-link"></i> Links</strong><br>
            <small>Easy hyperlink creation</small>
        </div>
        <div class="feature-item">
            <strong><i class="fa fa-code"></i> Code Blocks</strong><br>
            <small>Inline code and code blocks</small>
        </div>
        <div class="feature-item">
            <strong><i class="fa fa-quote-right"></i> Blockquotes</strong><br>
            <small>Styled quotations</small>
        </div>
    </div>
</article>

<!-- Example 1: Full Featured -->
<article>
    <h3>Full Featured Editor</h3>
    <p>This editor has all features enabled: images, links, code blocks, and all formatting options.</p>

    <div class="code-block">
        <code>$editor = $v->richtext("content")
    ->label("Full Featured Editor")
    ->placeholder("Try all features...")
    ->required();</code>
    </div>

    <form method="post">
        <?= $v1 ?>
        <?= $content_full->reset() ?>
        <button type="submit" name="example_full" value="1">
            <i class="fa fa-save"></i> Save Full Content
        </button>
    </form>

    <?php if ($content_full->value): ?>
        <details style="margin-top: 1rem;">
            <summary>View HTML Output</summary>
            <div class="code-block">
                <code><?= htmlspecialchars($content_full->value) ?></code>
            </div>
            <div
                style="padding: 1rem; border: 1px solid var(--pico-muted-border-color); border-radius: var(--pico-border-radius); margin-top: 1rem;">
                <strong>Rendered Output:</strong>
                <?= $content_full->value ?>
            </div>
        </details>
    <?php endif; ?>
</article>

<!-- Example 2: Simple Editor -->
<article>
    <h3>Simple Editor (No Images)</h3>
    <p>Perfect for when you want rich formatting but don't need image uploads.</p>

    <div class="code-block">
        <code>$editor = $v->richtext("content")
    ->enable_images(false)
    ->enable_code(false)
    ->min_height(150);</code>
    </div>

    <form method="post">
        <?= $v2 ?>
        <?= $content_simple->reset() ?>
        <button type="submit" name="example_simple" value="1">
            <i class="fa fa-save"></i> Save Simple Content
        </button>
    </form>
</article>

<!-- Example 3: Minimal Editor -->
<article>
    <h3>Minimal Editor (Custom Toolbar)</h3>
    <p>Highly customized with only specific formatting options.</p>

    <div class="code-block">
        <code>$editor = $v->richtext("content")
    ->toolbar([
        ['bold', 'italic'],
        [['list' => 'bullet']],
        ['clean']
    ])
    ->min_height(100);</code>
    </div>

    <form method="post">
        <?= $v3 ?>
        <?= $content_minimal->reset() ?>
        <button type="submit" name="example_minimal" value="1">
            <i class="fa fa-save"></i> Save Minimal Content
        </button>
    </form>
</article>

<!-- Example 4: Text Only -->
<article>
    <h3>Text & Links Only</h3>
    <p>Great for descriptions, comments, or documentation.</p>

    <div class="code-block">
        <code>$editor = $v->richtext("content")
    ->enable_images(false)
    ->enable_links(true)
    ->enable_code(true);</code>
    </div>

    <form method="post">
        <?= $v4 ?>
        <?= $content_text->reset() ?>
        <button type="submit" name="example_text" value="1">
            <i class="fa fa-save"></i> Save Text Content
        </button>
    </form>
</article>

<!-- Example 5: Session-Based Image Upload (NEW!) -->
<article>
    <h3>Session-Based Image Upload & Cleanup</h3>
    <p>Organize uploaded images by session ID for easy tracking and cleanup.</p>

    <div class="code-block">
        <code>$editor = $v->richtext("content")
    ->session_id("event-123")  // Custom session ID
    ->enable_images(true);

// In your form HTML (automatic cleanup):
&lt;form data-richtext-cleanup="true"&gt;

// Or call manually:
await quillManager.cleanupFormEditors(formElement);</code>
    </div>

    <form method="post" id="session-example-form" data-richtext-cleanup="true">
        <?= $v5 ?>
        <?= $content_session->reset() ?>
        <button type="submit" name="example_session" value="1">
            <i class="fa fa-save"></i> Save & Auto-Cleanup
        </button>
        <button type="button" onclick="testCleanup()" class="secondary">
            <i class="fa fa-broom"></i> Manual Cleanup Test
        </button>
    </form>

    <div style="margin-top: 1rem;">
        <strong>Two Ways to Cleanup:</strong>
        <ol style="margin: 0.5rem 0;">
            <li><strong>Automatic:</strong> Add <code>data-richtext-cleanup="true"</code> to your form tag</li>
            <li><strong>Manual:</strong> Call <code>quillManager.cleanupFormEditors(form)</code> or
                <code>cleanupOrphanedImages(editorId)</code>
            </li>
        </ol>
        <strong>Try it:</strong> Upload images, remove some, then click "Manual Cleanup Test" or submit the form
    </div>
</article>

<script>
    // Manual cleanup test (for demonstration)
    async function testCleanup() {
        if (!window.quillManager) {
            alert('Quill manager not loaded!');
            return;
        }

        try {
            const result = await quillManager.cleanupOrphanedImages('content_session_textarea-editor');
            console.log('Manual cleanup:', result);

            if (result.deleted_count > 0) {
                Toast.success(`Cleaned up ${result.deleted_count} orphaned image(s)!`);
            } else {
                Toast.info('No orphaned images to cleanup');
            }
        } catch (error) {
            console.error('Cleanup error:', error);
            Toast.error('Cleanup failed: ' + error.message);
        }
    }
</script>

<!-- Usage Tips -->
<article>
    <h3><i class="fa fa-lightbulb"></i> Usage Tips</h3>

    <h4>Image Upload Methods:</h4>
    <ul>
        <li><strong>Click:</strong> Click the image button in the toolbar</li>
        <li><strong>Drag & Drop:</strong> Drag image files directly into the editor</li>
        <li><strong>Paste:</strong> Copy an image (Ctrl+C) and paste (Ctrl+V) into the editor</li>
    </ul>

    <h4>Keyboard Shortcuts:</h4>
    <ul>
        <li><kbd>Ctrl+B</kbd> - Bold</li>
        <li><kbd>Ctrl+I</kbd> - Italic</li>
        <li><kbd>Ctrl+U</kbd> - Underline</li>
        <li><kbd>Ctrl+Shift+7</kbd> - Ordered list</li>
        <li><kbd>Ctrl+Shift+8</kbd> - Bullet list</li>
    </ul>

    <h4>Configuration Options:</h4>
    <ul>
        <li><code>->enable_images(false)</code> - Disable image uploads</li>
        <li><code>->enable_links(false)</code> - Disable link creation</li>
        <li><code>->enable_code(false)</code> - Disable code blocks</li>
        <li><code>->min_height(300)</code> - Set minimum editor height in pixels</li>
        <li><code>->toolbar([...])</code> - Customize toolbar buttons</li>
        <li><code>->required()</code> - Make field required</li>
        <li><code>->max_length(1000)</code> - Limit content length</li>
    </ul>

    <h4>Security:</h4>
    <ul>
        <li>✅ Script tags are automatically blocked</li>
        <li>✅ Only safe HTML tags are allowed</li>
        <li>✅ Image uploads are validated (type & size)</li>
        <li>✅ File size limit: 5MB per image</li>
        <li>✅ Supported formats: JPG, PNG, GIF, WebP</li>
    </ul>
</article>