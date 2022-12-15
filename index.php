<?php
if (empty($_SESSION['user_id'])) {
    redirect("login");
} else {
    //redirect("accueil");
    redirect("mes-inscriptions");
}
