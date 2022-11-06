<?php

require_once __DIR__ . '/utils/router.php';
require_once __DIR__ . '/utils/core.php';

get('/', 'index.php');
any('/login', 'pages/login.php');
get('/accueil', 'pages/accueil.php');
get('/mon-profil', 'pages/profil.php');
get('/mes-inscriptions', 'pages/inscriptions.php');
get('/les-licencies', 'pages/licencies.php');

// Logout
get('/logout', function () {
    $_SESSION = array();
    session_destroy();
    redirect("login");
});

// This route must be the last, this way is the fallback
any('/404', 'pages/404.php');
