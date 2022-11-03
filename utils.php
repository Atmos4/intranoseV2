<?php

function redirect($href)
{
    header("Location: " . $href);
    exit;
}
