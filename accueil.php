<?php

$title = "Accueil";
ob_start();
require_once "template/nav.php";

$content = ob_get_clean();
require "template/layout.php";
