<?php
restrict_access(Access::$ADD_EVENTS);
$conversation = em()->find(Conversation::class, Router::getParameter("conversation_id"));
if (!$conversation) {
    force_404("Conversation not found");
}
$back = "/messages/conversation/" . $conversation->id;

$v = new Validator(['name' => $conversation->name], action: "rename_conversation");
$name = $v->text("name")->label("Nom de la conversation")->placeholder("Laisser vide pour utiliser les noms des groupes");

if ($v->valid()) {
    $conversation->name = trim($name->value ?? "");
    em()->flush();
    Toast::success("Conversation renommée");
    redirect($back);
}

page("Renommer la conversation");
?>
<?= actions()->back($back) ?>
<form method="post">
    <?= $v->render_validation() ?>
    <div class="row">
        <?= $name->render() ?>
        <div class="col-auto">
            <button type="submit">Modifier</button>
        </div>
    </div>
</form>
