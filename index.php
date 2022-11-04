<?php

require_once "core/utils.php";

if (empty($_SESSION['user_id'])) {
    redirect("login");
}

redirect("accueil");
