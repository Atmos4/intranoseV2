<?php

require_once 'core/init.php';
require_once 'core/router.php';

route('/', 'pages/index.php');
route('/login', 'pages/login.php');

// Developement
route('/seed-db', 'pages/seed_db.php');
route('/reset-pw/$user_id', 'pages/reset_pw.php');

// Events
route('/evenements', 'pages/events/event_list.php');
route('/evenements/nouveau', 'pages/events/event_edit.php');
route('/evenements/$event_id/modifier', 'pages/events/event_edit.php');
route('/evenements/$event_id/ajouter-course', 'pages/events/race_edit.php');
route('/evenements/$event_id/course/$race_id', 'pages/events/race_edit.php');
route('/evenements/$event_id', 'pages/events/event_view.php');
route('/evenements/$event_id/inscription', 'pages/events/event_register.php');
route('/evenements/$event_id/publier', 'pages/events/event_publish.php');
route('/evenements/$event_id/supprimer', 'pages/events/event_delete.php');
// route('/download', 'uploads/download_file.php');

// Settings
route('/mon-profil', 'pages/settings/settings.php');
// Settings/users
route('/licencies/$user_id/modifier', 'pages/settings/settings.php');

// Users
route('/licencies', 'pages/users/user_list.php');
route('/licencies/add', 'pages/users/user_add.php');
route('/licencies/reactiver', 'pages/users/user_reactivate.php');
route('/licencies/$user_id', 'pages/users/user_view.php');
route('/licencies/$user_id/supprimer', 'pages/users/user_delete.php');

// Shared documents
// route('/documents', 'pages/shared_documents.php');
// route('/download_shared_files', 'uploads/shared_docs/download_shared_file.php');

// Logout
route('/logout', function () {
    session_destroy();
    redirect("login");
});

// Special route, see router.php
route('/404', 'pages/404.php');