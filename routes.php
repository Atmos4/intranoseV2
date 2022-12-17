<?php

require_once __DIR__ . '/utils/router.php';
require_once __DIR__ . '/utils/core.php';

get('/', 'index.php');
any('/login', 'pages/login.php');
// Disabling home page for now, we don't really need it yet
// get('/accueil', 'pages/accueil.php');
any('/mon-profil', 'pages/settings.php');
any('/mon-profil/changement-mdp', 'pages/settings_password_change.php');
any('/mon-profil/changement-login', 'pages/settings_login_change.php');
get('/evenements', 'pages/event_list.php');
get('/evenements/$id_depl', 'pages/event_view.php');
get('/evenements/$id_depl/inscription', 'pages/event_submit.php');
get('/licencies', 'pages/user_list.php');
get('/licencies/$user_id', 'pages/user_view.php');

// Logout
get('/logout', function () {
    session_destroy();
    redirect("login");
});

// Special route, see router.php
any('/404', 'pages/404.php');
