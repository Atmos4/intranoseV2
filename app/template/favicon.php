<!-- Favicon -->
<?php if (env("INTRANOSE")): ?>
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/intranose/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon/intranose/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon/intranose/favicon-16x16.png">
    <link rel="manifest" href="/assets/favicon/intranose/site.webmanifest">
    <link rel="mask-icon" href="/assets/favicon/intranose/safari-pinned-tab.svg" color="#28b432">
    <meta name="msapplication-TileColor" content="#00a300">
<?php else: ?>
    <link rel="icon" type="image/png" href="/assets/favicon/linklub/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/assets/favicon/linklub/favicon.svg" />
    <link rel="shortcut icon" href="/assets/favicon/linklub/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/linklub/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Linklub" />
    <link rel="manifest" href="/assets/favicon/linklub/site.webmanifest" />
<?php endif ?>

<meta name="theme-color" content="#fff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#13171f" media="(prefers-color-scheme: dark)">