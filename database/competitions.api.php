<?php
function get_competition($competition_id)
{
    return fetch_single("SELECT * FROM courses WHERE cid = ? LIMIT 1", $competition_id);
}

function create_or_edit_competition(string $name, string $date, string $location, int $event_id, int $competition_id = null)
{
    $result = false;
    if ($competition_id) {
        $result = query_db("UPDATE courses
            SET nom=?,date=?,lieu=?
            WHERE cid=? LIMIT 1;",
            $name,
            $date,
            $location,
            $competition_id
        );
    } else {
        $result = query_db("INSERT INTO courses(nom,date,lieu,id_depl)
            VALUES(?,?,?,?)",
            $name,
            $date,
            $location,
            $event_id
        );
    }
    if ($result)
        redirect("/evenements/$event_id");
}