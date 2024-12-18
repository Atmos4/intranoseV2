<?php

return function ($selectedClub) {
    if ($selectedClub): ?>
        <article class="notice">
            <b>The current configuration targets <code><?= $selectedClub ?></code></b><br>
            You can change this by manually editing the <code>.env</code> file.
        </article>
    <?php endif;
} ?>