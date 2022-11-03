<?php

require_once "utils.php";

if (empty($_SESSION['user_id'])) {
    redirect("login");
}

redirect("accueil");
