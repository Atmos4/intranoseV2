<?php
restrict_access();
$user = User::getMain();
$v = new Validator();
$description = $v->textarea("description")->placeholder("Description")->attributes(['height' => '200px'])->required();

page("Bugs et suggestions");

if ($v->valid()) {
    $new_user_feedback = new UserFeedback();
    $new_user_feedback->user = $user;
    $new_user_feedback->description = $description->value;
    em()->persist($new_user_feedback);
    em()->flush();
    Toast::success("Retour posté ✉️"); ?>

    <article>Merci pour ton retour !</article>

    <?php
    return;
}

?>

<article>
    <form method="post">
        <p>Descris ton problème ou ta suggestion ci-dessous !</p>
        <?= $v->render_validation() ?>
        <?= $description->render() ?>
        <button type="submit">Envoyer</button>
    </form>
</article>