<?php

require_once __DIR__ . '/vendor/autoload.php';
session_start();

// User control
require_once app_path() . "/components/user_control.php";
// Load env
require_once base_path() . "/engine/load_env.php";

// BIG TODO: REPLACE THIS SHIT ROUTER.

Router::add('/', 'pages/index');
Router::add('/login', 'pages/login');

// Developement
Router::add('/dev', 'pages/dev/dev_page');
Router::add('/dev/create-user', 'pages/dev/create_test_user');
// TODO: replace this
//Router::add('/dev/reset-pw/$user_id', 'pages/dev/reset_pw');
Router::add('/dev/send-email', 'pages/dev/send_test_email');
Router::add('/dev/test-ovh', 'pages/dev/test_ovh');
Router::add('/dev/ovh-mailing', 'pages/dev/ovh_mailing_lists');

// Events
Router::add('/evenements', 'pages/events/event_list');
Router::add('/evenements/nouveau', 'pages/events/event_edit');
Router::add('/evenements/$event_id/modifier', 'pages/events/event_edit');
Router::add('/evenements/$event_id/ajouter-course', 'pages/events/race_edit');
Router::add('/evenements/$event_id/course/$race_id', 'pages/events/race_edit');
Router::add('/evenements/$event_id/course/$race_id/inscrits', 'pages/events/race_registered_list');
Router::add('/evenements/$event_id', 'pages/events/event_view');
Router::add('/evenements/$event_id/inscription', 'pages/events/event_register');
Router::add('/evenements/$event_id/publier', 'pages/events/event_publish');
Router::add('/evenements/$event_id/supprimer', 'pages/events/event_delete');
Router::add('/evenements/$event_id/participants', 'pages/events/event_registered_list');
// Router::add('/download', 'uploads/download_file');

// Settings
Router::add('/mon-profil', 'pages/settings/settings');
// Settings/users
Router::add('/licencies/$user_id/modifier', 'pages/settings/settings');

// Users
Router::add('/licencies', 'pages/users/user_list.php');
Router::add('/licencies/ajouter', 'pages/users/user_add.php');
Router::add('/licencies/desactive', 'pages/users/user_list_deactivated.php');
Router::add('/licencies/$user_id', 'pages/users/user_view.php');
Router::add('/licencies/$user_id/desactiver', 'pages/users/user_deactivation_confirm.php');
Router::add('/licencies/$user_id/supprimer', 'pages/users/user_delete_confirm.php');
Router::add('/licencies/$user_id/creer-famille', 'pages/users/family_create.php');
Router::add('/familles', 'pages/users/family_list.php');
Router::add('/famille/$family_id', 'pages/users/family_view.php');
Router::add('/famille/$family_id/supprimer', 'pages/users/family_remove.php');
Router::add('/famille/$family_id/change/$member_id', 'pages/users/family_change.php');
Router::add('/famille/$family_id/supprimer/$member_id', 'pages/users/family_remove.php');
Router::add('/user-control/$user_id', 'pages/users/take_user_control.php');
Router::add('/activation', 'pages/tokens/user_activation.php');
Router::add('/reinitialiser-mot-de-passe', 'pages/tokens/send_reset_password.php');
Router::add('/nouveau-mot-de-passe', 'pages/tokens/reset_password.php');

// Shared documents
// Router::add('/documents', 'pages/shared_documents');
// Router::add('/download_shared_files', 'uploads/shared_docs/download_shared_file');

// Logout
Router::add('/logout', function () {
    session_destroy();
    redirect("login");
});

// Special route, see router
Router::add('/404', 'pages/404');