<?php
/** @return Race[] */
function getRaces(): array
{
    return em()->createQuery("SELECT r, e, re, c FROM Race r JOIN r.event e LEFT JOIN r.entries re LEFT JOIN r.categories c")->getResult();
}

/** @return Activity[] */
function getActivities(): array
{
    return em()->createQuery("SELECT a, c FROM Activity a LEFT JOIN a.categories c")->getResult();
}