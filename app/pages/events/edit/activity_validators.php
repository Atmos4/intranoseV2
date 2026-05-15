<?php

/**
 * Builds and registers all activity fields onto the given Validator instance.
 *
 * This is the single source of truth for activity field definitions and validation
 * constraints. Used by both event_edit_complex.php (POST processing) and
 * activity_edit_form.php (rendering).
 *
 * @param Validator  $v      The validator instance to register fields on.
 * @param int        $index  Activity index (used to build field name prefix).
 * @param string|null $event_start  Event start date string for min/max bounds (submitted or entity value).
 * @param string|null $event_end    Event end date string for min/max bounds (submitted or entity value).
 *
 * @return array {
 *   id: mixed,
 *   name: Field,
 *   type: Field,
 *   start_date: DateTimeField,
 *   end_date: DateTimeField,
 *   location_label: Field,
 *   location_url: Field,
 *   description: Field,
 *   deadline: DateTimeField,
 * }
 */
function build_activity_validator(Validator $v, int $index, ?string $event_start, ?string $event_end): array
{
    $p = "activity_{$index}_";

    $name = $v->text("{$p}name")->label("Nom de l'activité")->placeholder()->required();

    $type_array = ["RACE" => "Course", "TRAINING" => "Entraînement", "OTHER" => "Autre"];
    $type = $v->select("{$p}type")->options($type_array)->label("Type d'activité");

    $start_date = $v->date_time("{$p}start_date")
        ->label("Date de début")
        ->required();

    $end_date = $v->date_time("{$p}end_date")
        ->label("Date de fin")
        ->min($start_date->value, "Doit être après le départ")
        ->required();

    if ($event_start) {
        $start_date
            ->min($event_start, "Doit être après la date de début de l'événement", true)
            ->max($event_end, "Doit être avant la date de fin de l'événement", true);
        $end_date
            ->min($event_start, "Doit être après la date de début de l'événement", true)
            ->max($event_end, "Doit être avant la date de fin de l'événement", true);
    }

    $location_label = $v->text("{$p}location_label")->label("Nom du Lieu")->required();
    $location_url = $v->url("{$p}location_url")->label("URL du lieu");
    $description = $v->textarea("{$p}description")->label("Description de l'activité");

    $deadline = $v->date_time("{$p}deadline")
        ->max($start_date->value ? date_create($start_date->value)->format("Y-m-d H:i:s") : "", "Doit être avant le jour et l'heure de l'activité")
        ->label("Date limite d'inscription");

    return [
        "name" => $name,
        "type" => $type,
        "start_date" => $start_date,
        "end_date" => $end_date,
        "location_label" => $location_label,
        "location_url" => $location_url,
        "description" => $description,
        "deadline" => $deadline,
    ];
}
