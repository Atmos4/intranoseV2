<?php
if (isset($_POST['action']) and ($_POST['action'] == "reset-control")) {
    unset($_SESSION['controlled_user_id']);
}

function ControlNotice()
{ ?>
    <form class="control-notice" method="post">
        <input type="hidden" name="action" value="reset-control">
        <i class="fa fa-eye"></i>
        <?= User::getCurrent()->first_name . " " . User::getCurrent()->last_name ?>
        <button type="submit" class="outline contrast"><i class="fa fa-xmark"></i></button>
    </form>
<?php } ?>