<?php
if (isset($_POST['action']) and ($_POST['action'] == "reset-control")) {
    unset($_SESSION['controlled_user_id']);
}

function ControlNotice()
{ ?>
    <article class="notice control-notice horizontal">
        <span>
            Vous contrôlez actuellement
            <?= User::getCurrent()->first_name . " " . User::getCurrent()->last_name ?>
        </span>
        <form method="post">
            <input type="hidden" name="action" value="reset-control">
            <button role="link" type="submit" class="outline secondary">
                <i class="fa fa-stop"></i> Arrêter
            </button>
        </form>
    </article>
<?php } ?>