<?php
if (isset($_POST['action']) and ($_POST['action'] == "reset-control")) {
    unset($_SESSION['controlled_user_id']);
}

function ControlNotice()
{ ?>
    <div class="control-notice" data-theme="light">
        Vous contrôlez actuellement
        <?= User::getCurrent()->first_name . " " . User::getCurrent()->last_name ?>
        <form method="post">
            <input type="hidden" name="action" value="reset-control">
            <button type="submit" class="outline secondary">
                <i class="fa fa-stop"></i> Arrêter
            </button>
        </form>
    </div>
<?php } ?>