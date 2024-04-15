<?php
class UserModal
{
    static function props($id)
    {
        return <<<EOL
        hx-get="/licencies/$id" 
        hx-trigger="click,keyup[keyCode==13,keyCode==32]" 
        hx-target="#userViewDialogRoot"
        hx-swap="innerHTML"
        EOL;
    }

    static function renderRoot()
    {
        $id = get_query_param("user");
        $content = $id ? component(app_path() . "/pages/users/user_view_modal.php")->render(["user_id" => $id, "open" => !!$id]) : "";

        return <<<HTML
        <div id="userViewDialogRoot">
        $content
        </div>
        HTML;
    }

    static function triggerShowModal($id)
    {
        header("HX-Trigger-After-Settle: {\"showModal\":{\"modalId\":\"$id\"}}");
    }
}