<?php
restrict(dev_or_staging());

function DevButton($href, $label)
{
    return <<<EOL
    <li><a href="$href" role="button" class="contrast outline">$label</a></li>
    EOL;
}

page("Dev") ?>

<?php if (is_dev()): ?>
    <b>User control</b>
    <nav>
        <ul>
            <?= DevButton("/dev/create-user", "Créer utilisateur") ?>
            <?= DevButton("/dev/change-access", "Change access") ?>
        </ul>
    </nav>
<?php endif ?>

<b>APIs</b>
<nav>
    <ul>
        <?= DevButton("/dev/send-email", "Email") ?>
    </ul>
</nav>
<b>Tests</b>
<nav>
    <ul>
        <?= DevButton("/dev/toast", "Toasts") ?>
        <?= DevButton("/dev/random", "Random") ?>
    </ul>
</nav>
<b>Notifications</b>
<nav>
    <ul>
        <?= DevButton("/dev/notifications", "SW et Notifications") ?>
    </ul>
</nav>

<b>PWA debug</b>
<nav>
    <ul>
        <li><button id="pwa-support">display mode</button></li>
        <li><button id="notification-support">check notification</button></li>
    </ul>
</nav>

<script>
    function getPWADisplayMode() {
        if (document.referrer.startsWith('android-app://'))
            return 'twa';
        if (window.matchMedia('(display-mode: browser)').matches)
            return 'browser';
        if (window.matchMedia('(display-mode: standalone)').matches)
            return 'standalone';
        if (window.matchMedia('(display-mode: minimal-ui)').matches)
            return 'minimal-ui';
        if (window.matchMedia('(display-mode: fullscreen)').matches)
            return 'fullscreen';
        if (window.matchMedia('(display-mode: window-controls-overlay)').matches)
            return 'window-controls-overlay';

        return 'unknown';
    }

    function detectNotificationSupport() {
        const swSupport = "serviceWorker" in navigator ? "yes" : "no";
        const pushSupport = "PushManager" in window ? "yes" : "no";
        const notifSupport = "Notification" in window ? "yes" : "no";
        return `serviceWorker: ${swSupport}
PushManager: ${pushSupport}
Notification: ${notifSupport}`;
    }
    document.getElementById("pwa-support").addEventListener("click", () => alert(getPWADisplayMode()));
    document.getElementById("notification-support").addEventListener("click", () => alert(detectNotificationSupport()));
</script>