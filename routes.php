<?php

require_once __DIR__ . '/vendor/autoload.php';
session_start();
require_once __DIR__ . "/engine/setup.php";

// ---routes---
Router::add('/', 'pages/index');
Router::add('/login', 'pages/login');

// Developement
if (env("DEVELOPMENT")) {
    Router::add('/dev', 'pages/dev/dev_page');
    Router::add('/dev/create-user', 'pages/dev/create_test_user');
    Router::add('/dev/change-access', 'pages/dev/change_user_access');
    //Router::add('/dev/reset-pw/$user_id', 'pages/dev/reset_pw');
    Router::add('/dev/send-email', 'pages/dev/send_test_email');

    // --- ovh ---
    Router::add('/dev/ovh', 'pages/dev/ovh/main_page');
    Router::add('/dev/ovh/mailing-lists', 'pages/dev/ovh/mailing_lists');
    Router::add('/dev/ovh/mailing-list/$name', 'pages/dev/ovh/mailing_list_view');
    Router::add('/dev/ovh/redirections', 'pages/dev/ovh/redirections');
    Router::add('/dev/ovh/redirections/$id', 'pages/dev/ovh/redirections');
    Router::add('/dev/ovh/test-api', 'pages/dev/ovh/test_api.php');

    // --- migration ---
    Router::add('/dev/migrate', '../database/migrate_db');

    // ---experiments
    Router::add('/dev/toast', 'pages/dev/test_toast');
    Router::add('/dev/random', 'pages/dev/test_random');
}

// Events
Router::add('/evenements', 'pages/events/event_list/event_list');
Router::add('/evenements/passes', 'pages/events/event_list/past_events');
Router::add('/evenements/nouveau', 'pages/events/event_edit');
Router::add('/evenements/$event_id/modifier', 'pages/events/event_edit');
Router::add('/evenements/$event_id/ajouter-course', 'pages/events/race_edit');
Router::add('/evenements/$event_id/course/$race_id', 'pages/events/race_edit');
Router::add('/evenements/$event_id', 'pages/events/event_view');
Router::add('/evenements/$event_id/inscription', 'pages/events/event_register');
Router::add('/evenements/$event_id/publier', 'pages/events/event_publish');
Router::add('/evenements/$event_id/supprimer', 'pages/events/event_delete');

// Event entry lists
Router::add('/evenements/$event_id/participants', 'pages/events/entry_list/entry_list');
Router::add('/evenements/$event_id/participants/tabs', 'pages/events/entry_list/entry_list_tabs');
// Router::add('/download', 'uploads/download_file');

// Settings
Router::add('/mon-profil', 'pages/settings/settings');
// Settings/users
Router::add('/licencies/$user_id/modifier', 'pages/settings/settings');
// OVH Settings
Router::add('/licencies/$user_id/ovh', 'pages/settings/ovh_settings');
Router::add('/licencies/$user_id/ovh/redirections', 'pages/settings/ovh/redirections');
Router::add('/licencies/$user_id/ovh/mailing', 'pages/settings/ovh/mailing');

// Users
Router::add('/licencies', 'pages/users/user_list.php');
Router::add('/licencies/ajouter', 'pages/users/user_add.php');
Router::add('/licencies/desactive', 'pages/users/user_list_deactivated.php');
Router::add('/licencies/$user_id', 'pages/users/user_view_modal.php');
Router::add('/licencies/$user_id/desactiver', 'pages/users/user_deactivation_confirm.php');
Router::add('/licencies/$user_id/supprimer', 'pages/users/user_delete_confirm.php');
Router::add('/licencies/$user_id/creer-famille', 'pages/users/family_create.php');
Router::add('/familles', 'pages/users/family_list.php');
Router::add('/famille/$family_id', 'pages/users/family_view.php');
Router::add('/famille/$family_id/supprimer', 'pages/users/family_remove.php');
Router::add('/famille/$family_id/change/$member_id', 'pages/users/family_change.php');
Router::add('/famille/$family_id/supprimer/$member_id', 'pages/users/family_remove.php');
Router::add('/user-control/$user_id', 'pages/users/take_user_control.php');

// Tokens
Router::add('/activation', 'pages/tokens/user_activation.php');
Router::add('/reinitialiser-mot-de-passe', 'pages/tokens/send_reset_password.php');
Router::add('/nouveau-mot-de-passe', 'pages/tokens/reset_password.php');

// Shared documents
Router::add('/documents', 'pages/shared_documents/shared_documents');
Router::add('/documents/ajouter', 'pages/shared_documents/add_shared_document');
Router::add('/telecharger', 'uploads/download_file');
Router::add('/documents/$doc_id/supprimer', 'pages/shared_documents/shared_documents_delete_confirm');

//Report
Router::add('/feedback', 'pages/user_feedback_submit');
Router::add('/feedback-list', 'pages/user_feedback_list');
Router::add('/feedback-list/supprimer/$user_id', 'pages/user_feedback_list');

// Logout
Router::add('/logout', function () {
    session_destroy();
    redirect("login");
});

// Special route, see router
Router::add('/404', 'pages/404');