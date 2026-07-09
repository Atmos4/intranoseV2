<?php

require_once __DIR__ . '/ActivityForm.php';
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false, numeric: false);
$event = $event_id ? em()->find(Event::class, $event_id) : new Event();

$is_simple = get_query_param("is_simple", numeric: false) ?? ($event->id ? $event->type == EventType::Simple : false);
$activity_index = get_query_param("action");
// is_complex true only when called from the complex event form (not a single activity)
$is_complex = !$is_simple && $activity_index !== "single_activity";
$prefix = $is_complex ? "activity_{$activity_index}_" : "";
$category_count = get_query_param("{$prefix}category_count") ?? 0;
$activity_id = get_query_param("{$prefix}id");
$event_start = get_query_param("event_start_date", numeric: false) ?? ($event->id ? $event->start_date->format("Y-m-d H:i:s") : null);
$event_end = get_query_param("event_end_date", numeric: false) ?? ($event->id ? $event->end_date->format("Y-m-d H:i:s") : null);

$categories = [];
for ($i = 0; $i < $category_count; $i++) {
    $categories[$i] = [
        'id' => get_query_param("{$prefix}category_{$i}_id"),
        'entry_count' => get_query_param("{$prefix}category_{$i}_entry_count") ?? 0,
    ];
}

$v = new Validator([]);

$fields = build_activity_validator($v, $event_start, $event_end, $is_complex ? $activity_index : null);

$category_rows = [];
foreach ($categories as $index => $cat) {
    $category_rows[$index]['name'] = $v->text("{$prefix}category_{$index}_name")->required();
    $category_rows[$index]['toggle'] = $v->switch("{$prefix}category_{$index}_toggle")->set_labels(" ", "Supprimer");
    $category_rows[$index]['id'] = $cat['id'];
    $category_rows[$index]['entry_count'] = $cat['entry_count'];
}

render_activity_form(
    $fields,
    $category_rows,
    $categories,
    $v,
    $is_complex,
    $is_simple,
    $is_complex ? $activity_index : null,
    $activity_id,
    $event,
);
