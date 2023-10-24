<?php
class UserModal
{
    static function props($id)
    {
        return <<<EOL
        hx-get="/licencies/$id" 
        hx-trigger="click,keyup[keyCode==13,keyCode==32]" 
        hx-target="#userViewDialog"
        hx-on:show-modal="document.getElementById('userViewDialog').showModal()"
        EOL;
    }

    static function renderRoot()
    {
        $id = get_query_param("user");
        $content = $id ? Component::render(app_path() . "/pages/users/user_view_modal.php", ["user_id" => $id]) : "";
        $open = $id ? "open" : "";

        return <<<EOL
        <dialog id="userViewDialog" $open>
        $content
        </dialog>
        EOL;
    }
}