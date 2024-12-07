<?php

return function ($display = false) { ?>
    <?php if (!$display)
        include "assets/svg/linklub_icon.svg"; ?>
    <span>
        <?= $display ? config("name", "linklub") : "linklub" ?>
    </span>
<?php } ?>