<?php

require_once __DIR__ . '/vendor/autoload.php';
session_start();
require_once __DIR__ . "/engine/setup.php";

// ---routes---
Router::add('/', __DIR__ . '/app/pages/index.php');
Router::add('/login', __DIR__ . '/app/pages/login.php');

// Developement
if (is_dev() || env("STAGING")) {
    Router::add('/dev', __DIR__ . '/app/pages/dev/dev_page.php');
    Router::add('/dev/create-user', __DIR__ . '/app/pages/dev/create_test_user.php');
    Router::add('/dev/change-access', __DIR__ . '/app/pages/dev/change_user_access.php');
    //Router::add('/dev/reset-pw/$user_id', __DIR__.'/app/pages/dev/reset_pw.php');
    Router::add('/dev/send-email', __DIR__ . '/app/pages/dev/send_test_email.php');

    // ---experiments
    Router::add('/dev/toast', __DIR__ . '/app/pages/dev/test_toast.php');
    Router::add('/dev/random', __DIR__ . '/app/pages/dev/test_random.php');

    // SQLITE
    Router::add('/sqlite', __DIR__ . '/app/pages/dev/sqlite_db.php');
}

//Notifications
Router::add('/dev/notifications', __DIR__ . '/app/pages/dev/test_push_notifications.php');

//Admin
Router::add('/admin', __DIR__ . '/app/pages/admin/dashboard.php');
Router::add('/admin/backups', __DIR__ . '/app/pages/admin/backup_view.php');
Router::add('/admin/backups/download', __DIR__ . '/app/pages/admin/download_backup.php');
Router::add('/admin/logs', __DIR__ . '/app/pages/admin/list_logs.php');
Router::add('/admin/logs/$log_file', __DIR__ . '/app/pages/admin/view_logs.php');

// END OF DANGER ZONE

// Events
Router::add('/evenements', __DIR__ . '/app/pages/events/event_list/event_list.php');
Router::add('/evenements/passes', __DIR__ . '/app/pages/events/event_list/past_events.php');
Router::add('/evenements/nouveau', __DIR__ . '/app/pages/events/edit/event_edit.php');
Router::add('/evenements/event_form', __DIR__ . '/app/pages/events/edit/EventEditForm.php');
Router::add('/evenements/$event_id/modifier', __DIR__ . '/app/pages/events/edit/event_edit.php');
Router::add('/evenements/$event_id', __DIR__ . '/app/pages/events/view/event_view.php');
Router::add('/evenements/$event_id/inscription', __DIR__ . '/app/pages/events/register/event_register_complex.php');
Router::add('/evenements/$event_id/inscription_simple', __DIR__ . '/app/pages/events/register/event_register_simple.php');
Router::add('/evenements/$event_id/publier', __DIR__ . '/app/pages/events/event_publish.php');
Router::add('/evenements/$event_id/supprimer', __DIR__ . '/app/pages/events/delete/event_delete.php');
Router::add('/evenements/$event_id/event_form', __DIR__ . '/app/pages/events/edit/EventEditForm.php');

//Vehicles
Router::add('/evenements/$event_id/vehicule/nouveau', 'pages/vehicle/vehicle_edit');
Router::add('/evenements/$event_id/vehicule/$vehicle_id', 'pages/vehicle/vehicle_edit');
Router::add('/evenements/$event_id/vehicule/$vehicle_id/supprimer', 'pages/vehicle/vehicle_delete');
Router::add('/evenements/$event_id/vehicule/$vehicle_id/inscription/$user_id', 'pages/vehicle/vehicle_register');

// Event entry lists
Router::add('/evenements/$event_id/participants', __DIR__ . '/app/pages/events/entry_list/entry_list.php');
Router::add('/evenements/$event_id/participants/tabs', __DIR__ . '/app/pages/events/entry_list/entry_list_tabs.php');

// Activities
Router::add('/evenements/$event_id/activite/$activity_id/modifier', __DIR__ . '/app/pages/events/edit/activity_edit.php');
Router::add('/evenements/$event_id/activite/nouveau', __DIR__ . '/app/pages/events/edit/activity_edit.php');
Router::add('/evenements/$event_id/activite/$activity_id', __DIR__ . '/app/pages/events/view/activity_view.php');
Router::add('/evenements/$event_id/activite/$activity_id/supprimer', __DIR__ . '/app/pages/events/delete/activity_delete.php');

// Settings
Router::add('/mon-profil', __DIR__ . '/app/pages/settings/settings.php');
// Settings/users
Router::add('/licencies/$user_id/modifier', __DIR__ . '/app/pages/settings/settings.php');

// Users
Router::add('/licencies', __DIR__ . '/app/pages/users/user_list.php');
Router::add('/licencies/ajouter', __DIR__ . '/app/pages/users/user_add.php');
Router::add('/licencies/desactive', __DIR__ . '/app/pages/users/user_list_deactivated.php');
Router::add('/licencies/$user_id', __DIR__ . '/app/pages/users/user_view_modal.php');
Router::add('/licencies/$user_id/desactiver', __DIR__ . '/app/pages/users/user_deactivation_confirm.php');
Router::add('/licencies/$user_id/supprimer', __DIR__ . '/app/pages/users/user_delete_confirm.php');
Router::add('/licencies/$user_id/creer-famille', __DIR__ . '/app/pages/users/family_create.php');
Router::add('/licencies/$user_id/debug', __DIR__ . '/app/pages/users/user_admin_panel.php');
// Familles
Router::add('/familles', __DIR__ . '/app/pages/users/family_list.php');
Router::add('/famille/$family_id', __DIR__ . '/app/pages/users/family_view.php');
Router::add('/famille/$family_id/supprimer', __DIR__ . '/app/pages/users/family_remove.php');
Router::add('/famille/$family_id/change/$member_id', __DIR__ . '/app/pages/users/family_change.php');
Router::add('/famille/$family_id/supprimer/$member_id', __DIR__ . '/app/pages/users/family_remove.php');

Router::add('/user-control/$user_id', __DIR__ . '/app/pages/users/take_user_control.php');

// Tokens
Router::add('/activation', __DIR__ . '/app/pages/tokens/user_activation.php');
Router::add('/reinitialiser-mot-de-passe', __DIR__ . '/app/pages/tokens/send_reset_password.php');
Router::add('/nouveau-mot-de-passe', __DIR__ . '/app/pages/tokens/reset_password.php');

// Shared documents
Router::add('/documents', __DIR__ . '/app/pages/shared_documents/shared_documents.php');
Router::add('/documents/ajouter', __DIR__ . '/app/pages/shared_documents/add_shared_document.php');
Router::add('/telecharger', __DIR__ . '/app/pages/files/download_file.php');
Router::add('/documents/$doc_id/supprimer', __DIR__ . '/app/pages/shared_documents/shared_documents_delete_confirm.php');

// Messages
Router::add('/messages', __DIR__ . "/app/pages/messages/messages_overview.php");
Router::add('/messages/nouveau', __DIR__ . "/app/pages/messages//new/new_message.php");
Router::add('/messages/search-users', __DIR__ . "/app/pages/messages//new/search_users.php");
Router::add('/messages/direct/$user_id', __DIR__ . "/app/pages/messages/messages_direct.php");

//Report
Router::add('/feedback/nouveau', __DIR__ . '/app/pages/user_feedback_submit.php');
Router::add('/feedback-list', __DIR__ . '/app/pages/user_feedback_list.php');
Router::add('/feedback-list/supprimer/$user_id', __DIR__ . '/app/pages/user_feedback_list.php');

//Notifications
Router::add('/save-subscription', 'notifications/save_subscription');

// Logout
Router::add('/logout', function () {
    AuthService::create()->logout();
    redirect("/login");
});

// Special route, see router
Router::add('/404', __DIR__ . '/app/pages/404.php');