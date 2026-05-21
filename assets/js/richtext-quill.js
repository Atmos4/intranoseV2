/**
 * Quill Rich Text Editor Integration
 * Handles initialization, image uploads, and HTMX compatibility
 */

class QuillEditorManager {
  constructor() {
    this.editors = new Map();
    this.uploadInProgress = new Set();
  }

  /**
   * Initialize all rich text editors on the page
   */
  initAll(selector = ".richtext-editor") {
    // Check if Quill is loaded
    if (typeof Quill === "undefined") {
      console.warn("Quill is not loaded yet. Waiting...");
      setTimeout(() => this.initAll(selector), 100);
      return;
    }

    const editors = document.querySelectorAll(selector);
    console.log(`Found ${editors.length} rich text editor(s) to initialize`);

    editors.forEach((element) => {
      // Skip if already initialized
      if (element.dataset.initialized === "true") {
        console.log("Editor already initialized, skipping");
        return;
      }

      this.initEditor(element);
    });
  }

  /**
   * Initialize a single editor instance
   */
  initEditor(element) {
    const editorId = element.dataset.editorId;
    const textareaId = element.dataset.textareaId;

    console.log("Initializing editor:", { editorId, textareaId });

    const config = JSON.parse(element.dataset.config || "{}");

    // Use more reliable querySelector that handles special characters
    const editorContainer =
      element.querySelector(`[id="${editorId}"]`) ||
      element.querySelector(`#${CSS.escape(editorId)}`);
    const textarea =
      element.querySelector(`[id="${textareaId}"]`) ||
      element.querySelector(`#${CSS.escape(textareaId)}`);

    console.log("Found elements:", {
      container: !!editorContainer,
      textarea: !!textarea,
      containerEl: editorContainer,
      textareaEl: textarea,
    });

    if (!editorContainer || !textarea) {
      console.error("Quill editor: Missing container or textarea", {
        editorId,
        textareaId,
        foundContainer: !!editorContainer,
        foundTextarea: !!textarea,
        elementHTML: element.innerHTML.substring(0, 200),
      });
      return;
    }

    try {
      // Build Quill configuration
      const quillConfig = {
        theme: "snow",
        placeholder:
          textarea.getAttribute("placeholder") || "Commencez à écrire...",
        modules: {
          toolbar: {
            container: config.toolbar || this.getDefaultToolbar(config),
            handlers: {},
          },
        },
      };

      // Add custom image handler if images are enabled
      if (config.enableImages) {
        quillConfig.modules.toolbar.handlers.image = () =>
          this.imageHandler(editorId);
      }

      // Generate or retrieve unique session ID for this editor
      // This ID is used to organize uploads and persists across page loads
      let sessionId = config.sessionId || element.dataset.sessionId;
      if (!sessionId) {
        sessionId = this.generateSessionId();
        element.dataset.sessionId = sessionId;
      }

      console.log("Initializing Quill with config:", quillConfig);
      console.log("Editor session ID:", sessionId);

      // Initialize Quill
      const quill = new Quill(editorContainer, quillConfig);

      console.log("Quill initialized successfully:", quill);

      // Store the instance with tracking
      this.editors.set(editorId, {
        quill,
        textarea,
        config,
        sessionId,
        uploadedImages: [], // Track all uploaded images for cleanup
      });

      // Load initial content
      if (textarea.value) {
        quill.root.innerHTML = textarea.value;
      }

      // Sync content to textarea on changes
      quill.on("text-change", () => {
        textarea.value = quill.root.innerHTML;
        // Trigger change event for form validation
        textarea.dispatchEvent(new Event("change", { bubbles: true }));
      });

      // Handle paste events for images
      if (config.enableImages && quill.root) {
        quill.root.addEventListener("paste", (e) =>
          this.handlePaste(e, editorId),
        );
        quill.root.addEventListener("drop", (e) =>
          this.handleDrop(e, editorId),
        );
      }

      // Mark as initialized
      element.dataset.initialized = "true";

      // Fix dropdown click propagation issue - delay to ensure Quill is fully rendered
      setTimeout(() => {
        this.fixPickerDropdowns(element);
      }, 100);

      // Add keyboard shortcuts
      this.addKeyboardShortcuts(quill);

      console.log("Editor fully initialized");
    } catch (error) {
      console.error("Quill editor: Failed to initialize", editorId, error);
    }
  }

  /**
   * Get default toolbar configuration
   */
  getDefaultToolbar(config) {
    const toolbar = [["bold", "italic", "underline", "strike"]];

    if (config.enableCode) {
      toolbar.push(["blockquote", "code-block"]);
    } else {
      toolbar.push(["blockquote"]);
    }

    toolbar.push([{ list: "ordered" }, { list: "bullet" }]);
    toolbar.push([{ header: [1, 2, 3, false] }]);

    const buttons = [];
    if (config.enableLinks) {
      buttons.push("link");
    }
    if (config.enableImages) {
      buttons.push("image");
    }
    if (buttons.length > 0) {
      toolbar.push(buttons);
    }

    toolbar.push(["clean"]);

    return toolbar;
  }

  /**
   * Generate a unique session ID for an editor
   */
  generateSessionId() {
    const timestamp = Date.now().toString(36);
    const randomStr = Math.random().toString(36).substring(2, 9);
    return `${timestamp}-${randomStr}`;
  }

  /**
   * Fix dropdown click propagation to prevent immediate closing
   */
  fixPickerDropdowns(element) {
    const toolbar = element.querySelector(".ql-toolbar");
    if (!toolbar) {
      console.warn("Toolbar not found for picker fix");
      return;
    }

    // CRITICAL FIX: Stop propagation from the entire editor container
    // This prevents clicks on the hidden textarea from closing dropdowns
    element.addEventListener(
      "click",
      (e) => {
        console.log(
          "Editor container click, stopping propagation from:",
          e.target,
        );
        e.stopPropagation();
      },
      false,
    );

    element.addEventListener(
      "mousedown",
      (e) => {
        console.log(
          "Editor container mousedown, stopping propagation from:",
          e.target,
        );
        e.stopPropagation();
      },
      false,
    );
  }

  /**
   * Custom image handler - triggers file input
   */
  imageHandler(editorId) {
    const input = document.createElement("input");
    input.setAttribute("type", "file");
    input.setAttribute(
      "accept",
      "image/png, image/jpeg, image/gif, image/webp",
    );
    input.setAttribute("multiple", "multiple");

    input.onchange = async () => {
      const files = Array.from(input.files);
      for (const file of files) {
        await this.uploadImage(file, editorId);
      }
    };

    input.click();
  }

  /**
   * Handle paste events for images
   */
  async handlePaste(e, editorId) {
    const clipboardData = e.clipboardData || window.clipboardData;
    const items = clipboardData.items;

    let hasImage = false;
    for (let i = 0; i < items.length; i++) {
      if (items[i].type.indexOf("image") !== -1) {
        hasImage = true;
        e.preventDefault();
        const file = items[i].getAsFile();
        await this.uploadImage(file, editorId);
      }
    }
  }

  /**
   * Handle drop events for images
   */
  async handleDrop(e, editorId) {
    console.log("Droping image");
    const dataTransfer = e.dataTransfer;
    if (dataTransfer && dataTransfer.files && dataTransfer.files.length > 0) {
      const files = Array.from(dataTransfer.files).filter((file) =>
        file.type.startsWith("image/"),
      );

      if (files.length > 0) {
        e.preventDefault();
        for (const file of files) {
          await this.uploadImage(file, editorId);
        }
      }
    }
  }

  /**
   * Upload an image file
   */
  async uploadImage(file, editorId) {
    const editor = this.editors.get(editorId);
    if (!editor) return;

    const { quill, config } = editor;
    const range = quill.getSelection(true);

    // Check file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      this.showError("L'image est trop volumineuse (max 5MB)");
      return;
    }

    // Check file type
    if (!file.type.match(/^image\/(png|jpeg|gif|webp)$/)) {
      this.showError(
        "Type de fichier non supporté. Utilisez PNG, JPEG, GIF ou WebP.",
      );
      return;
    }

    // Mark upload in progress
    this.uploadInProgress.add(editorId);

    // Insert loading placeholder
    const loadingText = "Téléversement en cours...";
    quill.insertText(range.index, loadingText, { italic: true, color: "#666" });
    quill.setSelection(range.index + loadingText.length);

    try {
      const formData = new FormData();
      formData.append("image", file);
      formData.append("session_id", editor.sessionId);

      const response = await fetch("/api/upload-editor-image", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      console.log(result);

      // Remove loading text
      quill.deleteText(range.index, loadingText.length);

      if (result.success) {
        // Track the uploaded image
        editor.uploadedImages.push(result.url);

        // Insert the uploaded image
        quill.insertEmbed(range.index, "image", result.url);
        quill.setSelection(range.index + 1);

        console.log("Image uploaded successfully:", result.filename);
      } else {
        this.showError(result.error || "Échec du téléversement");
        quill.setSelection(range.index);
      }
    } catch (error) {
      // Remove loading text
      quill.deleteText(range.index, loadingText.length);
      quill.setSelection(range.index);

      console.error("Image upload error:", error);
      this.showError("Erreur réseau lors du téléversement");
    } finally {
      this.uploadInProgress.delete(editorId);
    }
  }

  /**
   * Add useful keyboard shortcuts
   */
  addKeyboardShortcuts(quill) {
    // Ctrl+B for bold (already handled by Quill)
    // Ctrl+I for italic (already handled by Quill)
    // Ctrl+U for underline (already handled by Quill)
    // Add any custom shortcuts here if needed
  }

  /**
   * Show error message
   */
  showError(message) {
    // Use your project's toast/notification system if available
    if (window.Toast && Toast.error) {
      Toast.error(message);
    } else {
      alert(message);
    }
  }

  /**
   * Extract image URLs from editor content
   */
  getReferencedImages(editorId) {
    const editor = this.editors.get(editorId);
    if (!editor) return [];

    const { quill } = editor;
    const content = quill.root.innerHTML;

    // Extract all image src attributes
    const imgRegex = /<img[^>]+src="([^">]+)"/g;
    const urls = [];
    let match;

    while ((match = imgRegex.exec(content)) !== null) {
      urls.push(match[1]);
    }

    return urls;
  }

  /**
   * Clean up orphaned images that were uploaded but not used in final content
   * Call this when saving/submitting a form
   */
  async cleanupOrphanedImages(editorId) {
    const editor = this.editors.get(editorId);
    if (!editor) {
      console.warn("Editor not found:", editorId);
      return { success: false, error: "Editor not found" };
    }

    const { sessionId, uploadedImages } = editor;

    // Get images that are still referenced in the content
    const referencedImages = this.getReferencedImages(editorId);

    try {
      const response = await fetch("/api/cleanup-editor-images", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          session_id: sessionId,
          referenced_urls: referencedImages,
        }),
      });

      const result = await response.json();

      if (result.success && result.deleted_count > 0) {
        console.log(
          `Cleaned up ${result.deleted_count} orphaned image(s)`,
          result.deleted_files,
        );
      }

      return result;
    } catch (error) {
      console.error("Image cleanup error:", error);
      return { success: false, error: error.message };
    }
  }

  /**
   * Get all uploaded images for an editor (for debugging/tracking)
   */
  getUploadedImages(editorId) {
    const editor = this.editors.get(editorId);
    return editor ? editor.uploadedImages : [];
  }

  /**
   * Get the session ID for an editor
   */
  getSessionId(editorId) {
    const editor = this.editors.get(editorId);
    return editor ? editor.sessionId : null;
  }

  /**
   * Find all rich text editor IDs within a form or element
   */
  getEditorsInForm(formElement) {
    const editors = formElement.querySelectorAll(".richtext-editor");
    return Array.from(editors).map((el) => el.dataset.editorId);
  }

  /**
   * Cleanup all editors in a form
   */
  async cleanupFormEditors(formElement) {
    const editorIds = this.getEditorsInForm(formElement);
    const results = [];

    for (const editorId of editorIds) {
      try {
        const result = await this.cleanupOrphanedImages(editorId);
        results.push({ editorId, ...result });
      } catch (error) {
        console.warn(`Cleanup failed for ${editorId}:`, error);
        results.push({ editorId, success: false, error: error.message });
      }
    }

    return results;
  }

  /**
   * Setup automatic cleanup on form submission
   * Call this to automatically cleanup orphaned images when a form is submitted
   */
  setupFormCleanup(formSelector, options = {}) {
    const form =
      typeof formSelector === "string"
        ? document.querySelector(formSelector)
        : formSelector;

    if (!form) {
      console.warn("Form not found:", formSelector);
      return;
    }

    const {
      delay = 1000, // Wait time after submit before cleanup
      showToast = false, // Show toast notification on cleanup
    } = options;

    form.addEventListener("submit", async (e) => {
      // Let the form submit naturally, cleanup happens in background
      setTimeout(async () => {
        try {
          const results = await this.cleanupFormEditors(form);
          const totalDeleted = results.reduce(
            (sum, r) => sum + (r.deleted_count || 0),
            0,
          );

          if (totalDeleted > 0) {
            console.log(
              `Cleaned up ${totalDeleted} orphaned image(s) from ${results.length} editor(s)`,
            );

            if (showToast && window.Toast && Toast.success) {
              Toast.success(`Cleaned up ${totalDeleted} orphaned image(s)`);
            }
          }
        } catch (error) {
          console.error("Form cleanup error:", error);
        }
      }, delay);
    });

    console.log("Cleanup handler attached to form:", form);
  }

  /**
   * Get editor instance by ID
   */
  getEditor(editorId) {
    return this.editors.get(editorId);
  }

  /**
   * Destroy an editor instance
   */
  destroyEditor(editorId) {
    const editor = this.editors.get(editorId);
    if (editor) {
      // Quill doesn't have a built-in destroy method
      // Just remove from our map
      this.editors.delete(editorId);
    }
  }

  /**
   * Destroy all editors
   */
  destroyAll() {
    this.editors.clear();
    this.uploadInProgress.clear();
  }
}

// Create global instance
const quillManager = new QuillEditorManager();

// Auto-initialize on DOM ready
function initializeQuillManagement() {
  quillManager.initAll();

  // Setup automatic cleanup for forms with data-richtext-cleanup attribute
  const formsWithCleanup = document.querySelectorAll(
    'form[data-richtext-cleanup="true"]',
  );
  formsWithCleanup.forEach((form) => {
    quillManager.setupFormCleanup(form);
  });

  // Re-initialize for HTMX dynamic content
  if (document.body) {
    document.body.addEventListener("htmx:afterSwap", () => {
      quillManager.initAll();

      // Setup cleanup for dynamically loaded forms
      const newForms = document.querySelectorAll(
        'form[data-richtext-cleanup="true"]',
      );
      newForms.forEach((form) => {
        // Check if cleanup is already attached to avoid duplicates
        if (!form.dataset.cleanupAttached) {
          quillManager.setupFormCleanup(form);
          form.dataset.cleanupAttached = "true";
        }
      });
    });

    // Re-initialize after HTMX content settles
    document.body.addEventListener("htmx:afterSettle", () => {
      quillManager.initAll();
    });
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeQuillManagement);
} else if (
  document.readyState === "interactive" ||
  document.readyState === "complete"
) {
  // DOM is already ready
  if (document.body) {
    initializeQuillManagement();
  } else {
    // Wait for body to be available
    document.addEventListener("DOMContentLoaded", initializeQuillManagement);
  }
}

// Export for manual usage
window.QuillEditorManager = QuillEditorManager;
window.quillManager = quillManager;
