<?php

/** Get the courses */
function get_events()
{
    return fetch(
        "SELECT * FROM deplacements 
        ORDER BY depart DESC;"
    );
}

function is_registered($deplacement, $id_runner)
{
    $user_data = fetch(
        "SELECT date
        FROM inscriptions_depl
        WHERE id_depl=? AND id_runner=? LIMIT 1;",
        $deplacement["did"],
        $id_runner
    );
    if (count($user_data)) {
        return true;
    } else return false;
}

function get_event_by_id($deplacement_id, $runner_id)
{
    return fetch_single(
        "SELECT deplacements.*, depl.* FROM deplacements 
        LEFT JOIN inscriptions_depl as depl
            ON depl.id_depl = deplacements.did 
            AND depl.id_runner = ?
        WHERE did = ?
        ORDER BY depart DESC LIMIT 1;",
        $runner_id,
        $deplacement_id
    );
}

function get_competitions_by_event_id($deplacement_id, $runner_id)
{
    return fetch(
        "SELECT courses.*, inscriptions_courses.present as present FROM courses 
        LEFT JOIN inscriptions_courses 
            ON inscriptions_courses.id_course = courses.cid 
            AND inscriptions_courses.id_runner = ?
        WHERE courses.id_depl = ?
        ORDER BY date ASC;",
        $runner_id,
        $deplacement_id
    );
}
