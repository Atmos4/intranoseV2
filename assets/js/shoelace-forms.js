/**
 * shoelace-forms.js
 *
 * Handles form submissions for pages using Shoelace components.
 * Shoelace web components (like sl-select) don't automatically participate in form submissions
 * because they use Shadow DOM. This script serializes their values into hidden inputs
 * so they can be properly submitted with the form.
 */

/**
 * Serializes a single Shoelace select component into hidden inputs
 */
function serializeShoelaceSelect(form, select) {
  const name = select.getAttribute("name");

  // Remove existing hidden inputs for this select to avoid duplicates
  form
    .querySelectorAll(`input[type="hidden"][data-sl-field="${name}"]`)
    .forEach((i) => i.remove());

  const value = select.value;

  if (Array.isArray(value)) {
    // Multiple select - create one input for each selected value
    value.forEach((val) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = name;
      input.value = val;
      input.setAttribute("data-sl-field", name);
      form.appendChild(input);
    });
  } else if (value) {
    // Single select
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = value;
    input.setAttribute("data-sl-field", name);
    form.appendChild(input);
  }
}

/**
 * Initializes form submission handling for a single form
 */
function initShoelaceForm(form) {
  form.addEventListener("submit", (e) => {
    // Serialize all sl-select components in this form
    form.querySelectorAll("sl-select[name]").forEach((select) => {
      serializeShoelaceSelect(form, select);
    });
  });
}

/**
 * Initializes all forms on the page
 * Can be called with a specific form selector or without arguments to handle all forms
 */
function initShoelaceForms(selector = "form") {
  document.querySelectorAll(selector).forEach((form) => {
    initShoelaceForm(form);
  });
}

// Auto-initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => initShoelaceForms());
} else {
  // DOM already loaded
  initShoelaceForms();
}

// Re-initialize forms after HTMX content swaps
document.body.addEventListener("htmx:afterSwap", (event) => {
  // Find any new forms in the swapped content
  const target = event.detail.target;
  if (target.tagName === "FORM") {
    initShoelaceForm(target);
  } else {
    target.querySelectorAll("form").forEach((form) => {
      initShoelaceForm(form);
    });
  }
});

// Export for manual initialization if needed
window.initShoelaceForms = initShoelaceForms;
window.initShoelaceForm = initShoelaceForm;
