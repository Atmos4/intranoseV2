<?php

return function ($selectedClub) {
    if ($selectedClub): ?>
        <article class="notice">
            <b>Multi-club is a work in progress</b><br>
            The current configuration targets <code><?= $selectedClub ?></code><br>
            <i>You can only change this by manually editing the <code>.env</code> file.</i>
        </article>
    <?php endif;
} ?>