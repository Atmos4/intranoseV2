<?php
// These scripts load shoelace from CDN. The files should be cached forever so it is very convenient.

?>
<link rel="stylesheet" media="(prefers-color-scheme:light)"
    href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/themes/light.css" />
<link rel="stylesheet" media="(prefers-color-scheme:dark)"
    href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/themes/dark.css"
    onload="document.documentElement.classList.add('sl-theme-dark');" />
<script type="module">
    // Import all shoelace components here
    import 'https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/components/copy-button/copy-button.js';
    import 'https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/components/tooltip/tooltip.js';
    import 'https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/components/tab-group/tab-group.js';
    import 'https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/components/tab/tab.js';
    import 'https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.18.0/cdn/components/tab-panel/tab-panel.js';
</script>