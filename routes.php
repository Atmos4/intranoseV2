<?php

require_once __DIR__ . '/utils/router.php';
require_once __DIR__ . '/utils/core.php';

get('/', 'index.php');
any('/login', 'pages/login.php');
// Disabling home page for now, we don't really need it yet
// get('/accueil', 'pages/accueil.php');
any('/mon-profil', 'pages/profil.php');
get('/mes-inscriptions', 'pages/inscriptions_list.php');
get('/mes-inscriptions/details/$id_depl', 'pages/inscription_view.php');
get('/les-licencies', 'pages/licencies_list.php');
get('/les-licencies/details/$licencie_id', 'pages/licencie_view.php');
any('/mon-profil/changement-mdp', 'pages/profil_password_change.php');
any('/mon-profil/changement-login', 'pages/profil_login_change.php');

// Logout
get('/logout', function () {
    session_destroy();
    redirect("login");
});

// Special route, see router.php
any('/404', 'pages/404.php');
