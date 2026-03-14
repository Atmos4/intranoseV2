<?php
include_once __DIR__ . "/RenderEvents.php";

restrict_access();
formatter("d MMM");
$user = User::getCurrent();

// Pagination parameters
$page_param = get_query_param('page');
$page = isset($page_param) ? (int) $page_param : 1;
$limit = 100;
$offset = ($page - 1) * $limit;

// Fetch paginated events
$past_events = EventService::listPastOpenPaginated($user->id, $limit, $offset);
$has_more = count($past_events) === $limit;

if ($page === 1) {
    page("Évenements passés")->css("event_list.css");
}
?>
<?php if ($page === 1): ?>
    <?= actions()->back("/evenements"); ?>
<?php endif; ?>

<?php
if (count($past_events)) {
    foreach ($past_events as $event) {
        render_events($event);
    }
} else if ($page === 1) { ?>
        <p>Pas d'événements passés 😿</p>
<?php } ?>

<?php if ($has_more): ?>
    <div id="load-more-trigger" hx-get="/evenements/passes?page=<?= $page + 1 ?>" hx-trigger="revealed" hx-swap="outerHTML">
        <p class="center"><i class="fas fa-spinner fa-spin"></i> Chargement...</p>
    </div>
<?php endif; ?>