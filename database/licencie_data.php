<?php

/** Get all active users */
function get_all_licencies()
{
    return fetch(
        "SELECT * FROM licencies 
        WHERE valid=1 AND invisible=0 
        ORDER BY nom"
    );
}
/** Get a user,  */
function get_licencie($id)
{
    return fetch_single(
        "SELECT li.*, cat.name as category_name
        FROM licencies li 
        LEFT JOIN categories cat ON cat.cid = li.categorie 
        WHERE id = ? LIMIT 1",
        $id
    );
}
