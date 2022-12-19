<?php
require_once "utils/form_validation.php";

function create_event($post)
{
    if ($validator = validate($post)) {
        $validator->string("event_name")->required();
        $start_date = $validator->date("start_date")->required();
        $validator->date("end_date")->required()->after($start_date->value);
        $validator->date("limit_date")->required()->before($start_date->value);
    }
    return $validator;
}
