<?php
$token = AccessToken::retrieve($_GET['token'] ?? "");
page("Activer le compte") ?>
<article>
    <p>
        Vous activez le compte de
        <?= "{$token->user->first_name} {$token->user->last_name}" ?>.
    </p>
    <p>Ce token expire dans
        <?= date_create()->diff($token->expiration)->format('%i minutes et %s secondes') ?>
    </p>
</article>