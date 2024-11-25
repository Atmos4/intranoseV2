<script>
    window.firebaseApiKey = "<?= env("FIREBASE_API_KEY") ?>";
    window.conversationId = "<?= $conversation->id ?>";
    window.conversationCollection = "<?= implode("_", [env("FIREBASE_COL_PREFIX") ?? "dev", "conversations"]) ?>";
</script>
<script type="module" src="/assets/js/message_connection.js">
</script>