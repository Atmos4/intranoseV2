<?php

/** Get the courses */
function get_deplacements()
{
    return fetch(
        "SELECT * FROM deplacements 
        ORDER BY depart DESC"
    );
}
