<?php
function SubmitButton($action, $text, $polling = false)
{
    $pollAttrs = $polling ? "aria-busy='true' disabled" : "data-loading-aria-busy data-loading-disable";
    return <<<EOL
    <button name="action" value="$action" class="outline contrast" $pollAttrs>$text</button>
    EOL;
}