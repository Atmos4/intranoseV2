<?php
if (empty($_SESSION['user_id']) || !em()->find(User::class, $_SESSION['user_id'])->active) {
    redirect("login");
} else {
    //redirect("accueil");
    redirect("evenements");
}