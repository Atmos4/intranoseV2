<?php

return function ($displayLogo = false) { ?>
    <?php if (!$displayLogo)
        include "assets/svg/linklub_icon.svg"; ?>
    <span>
        <?= config("name", "linklub") ?>
    </span>
<?php } ?>