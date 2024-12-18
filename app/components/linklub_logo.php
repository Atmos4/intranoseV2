<?php

return function ($displayLogo = true) { ?>
    <?php if ($displayLogo)
        include "assets/svg/linklub_icon.svg"; ?>
    <span>
        <?= config("name", "linklub") ?>
    </span>
<?php } ?>