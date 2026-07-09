<?php

require_once __DIR__ . '/GuardianForm.php';
restrict_access();

$index = get_query_param("index") ?? 0;
$v = new Validator([]);
$fields = build_guardian_validator($v, "new_guardians[$index]");
render_guardian_fieldset($fields, "Nouveau tuteur " . ($index + 1), js_delete: true);
