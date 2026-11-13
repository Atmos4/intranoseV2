<?php

require_once __DIR__ . '/ActivityForm.php';
restrict_access(Access::$ADD_EVENTS);

$event_id = get_route_param("event_id", strict: false, numeric: false);
$event = $event_id ? em()->find(Event::class, $event_id) : new Event();

$is_simple = get_query_param("is_simple", numeric: false) ?? ($event->id ? $event->type == EventType::Simple : false);
$activity_index = get_query_param("action");
// is_complex true only when called from the complex event form (not a single activity)
$is_complex = !$is_simple && $activity_index !== "single_activity";
$query_prefix = $is_complex ? "activity_{$activity_index}_" : "";
$category_count = get_query_param("{$query_prefix}category_count") ?? 0;
$activity_id = get_query_param("{$query_prefix}id");
$event_start = get_query_param("event_start_date", numeric: false) ?? ($event->id ? $event->start_date->format("Y-m-d H:i:s") : null);
$event_end = get_query_param("event_end_date", numeric: false) ?? ($event->id ? $event->end_date->format("Y-m-d H:i:s") : null);

// Seed empty rows so the collection finds $category_count rows to declare;
// name/toggle values themselves aren't carried across query params.
$seed = [];
if ($is_complex && $activity_id) {
    $seed["activity"][$activity_index]["id"] = $activity_id;
}
for ($i = 0; $i < $category_count; $i++) {
    $row_seed = ["id" => get_query_param("{$query_prefix}category_{$i}_id")];
    if ($is_complex) {
        $seed["activity"][$activity_index]["category"][$i] = $row_seed;
    } else {
        $seed["category"][$i] = $row_seed;
    }
}

$v = new Validator($seed);
$builder = $is_complex ? new FieldRow($v, "activity[{$activity_index}]") : $v;

$fields = build_activity_validator($builder, $event_start, $event_end);

$categories = $builder->collection("category");
$categories->hidden("id");
$categories->text("name")->required();
$categories->switch("toggle")->set_labels(" ", "Supprimer");

$category_rows = [];
foreach ($categories as $index => $row) {
    $category_rows[$index] = [
        'entry_count' => get_query_param("{$query_prefix}category_{$index}_entry_count") ?? 0,
    ];
}

render_activity_form(
    $fields,
    $category_rows,
    $categories,
    $builder,
    $is_complex,
    $is_simple,
    $is_complex ? $activity_index : null,
    $event,
);
