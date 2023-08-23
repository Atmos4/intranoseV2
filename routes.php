<?php

require_once 'core/init.php';
require_once 'core/router.php';

route('/', 'pages/index');
route('/login', 'pages/login');

// Developement
route('/dev', 'pages/dev/dev_page');
route('/dev/create-user', 'pages/dev/create_test_user');
// TODO: replace this
//route('/dev/reset-pw/$user_id', 'pages/dev/reset_pw');
route('/dev/send-email', 'pages/dev/send_test_email');

// Events
route('/evenements', 'pages/events/event_list');
route('/evenements/nouveau', 'pages/events/event_edit');
route('/evenements/$event_id/modifier', 'pages/events/event_edit');
route('/evenements/$event_id/ajouter-course', 'pages/events/race_edit');
route('/evenements/$event_id/course/$race_id', 'pages/events/race_edit');
route('/evenements/$event_id', 'pages/events/event_view');
route('/evenements/$event_id/inscription', 'pages/events/event_register');
route('/evenements/$event_id/publier', 'pages/events/event_publish');
route('/evenements/$event_id/supprimer', 'pages/events/event_delete');
// route('/download', 'uploads/download_file');

// Settings
route('/mon-profil', 'pages/settings/settings');
// Settings/users
route('/licencies/$user_id/modifier', 'pages/settings/settings');

// Users
route('/licencies', 'pages/users/user_list.php');
route('/licencies/ajouter', 'pages/users/user_add.php');
route('/licencies/desactive', 'pages/users/user_list_deactivated.php');
route('/licencies/$user_id', 'pages/users/user_view.php');
route('/licencies/$user_id/supprimer', 'pages/users/user_delete.php');
route('/licencies/$user_id/creer-famille', 'pages/users/family_create.php');
route('/familles', 'pages/users/family_list.php');
route('/famille/$family_id', 'pages/users/family_view.php');
route('/famille/$family_id/supprimer', 'pages/users/family_remove.php');
route('/famille/$family_id/change/$member_id', 'pages/users/family_change.php');
route('/famille/$family_id/supprimer/$member_id', 'pages/users/family_remove.php');
route('/user-control/$user_id', 'pages/users/take_user_control.php');
route('/activation', 'pages/tokens/user_activation.php');
route('/reinitialiser-mot-de-passe', 'pages/tokens/send_reset_password.php');
route('/nouveau-mot-de-passe', 'pages/tokens/reset_password.php');

// Shared documents
// route('/documents', 'pages/shared_documents');
// route('/download_shared_files', 'uploads/shared_docs/download_shared_file');

// Logout
route('/logout', function () {
    session_destroy();
    redirect("login");
});

// Special route, see router
route('/404', 'pages/404');