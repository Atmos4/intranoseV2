<?php

require_once __DIR__ . '/utils/router.php';
require_once __DIR__ . '/utils/core.php';

get('/', 'index.php');
any('/login', 'pages/login.php');
get('/accueil', 'pages/accueil.php');
any('/mon-profil', 'pages/profil.php');
get('/mes-inscriptions', 'pages/inscriptions.php');
get('/les-licencies', 'pages/licencies_list.php');
get('/les-licencies/details/$licencie_id', 'pages/licencie_view.php');
any('/mon-profil/changement-mdp', 'pages/password_change.php');

// Logout
get('/logout', function () {
    $_SESSION = array();
    session_destroy();
    redirect("login");
});

// Special route, see router.php
any('/404', 'pages/404.php');
