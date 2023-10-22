<?php
class UserModal
{
    static function props($id)
    {
        $userIdFromQuery = $_GET['user'] ?? null;
        $shouldLoad = $userIdFromQuery == $id ? ",load" : "";
        return <<<EOL
        hx-get="/licencies/$id" 
        hx-trigger="click,keyup[keyCode==13,keyCode==32]$shouldLoad" 
        hx-target="#userViewDialog"
        hx-on:show-modal="document.getElementById('userViewDialog').showModal()"
        EOL;
    }

    static function renderRoot()
    {
        return <<<EOL
        <dialog id="userViewDialog"></dialog>
        EOL;
    }
}