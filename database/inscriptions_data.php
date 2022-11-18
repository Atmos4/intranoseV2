<?php

/** Get the courses */
function get_deplacements()
{
    return fetch(
        "SELECT * FROM deplacements 
        ORDER BY depart DESC"
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
