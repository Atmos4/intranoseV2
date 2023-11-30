<?php
restrict_access();
$user = User::getMain();
$v = new Validator();
$description = $v->textarea("description")->placeholder("Description")->attributes(['height' => '200px'])->required();

if ($v->valid()) {
    $new_user_feedback = new UserFeedback();
    $new_user_feedback->user = $user;
    $new_user_feedback->description = $description->value;
    em()->persist($new_user_feedback);
    em()->flush();
    Toast::success("Merci pour votre retour !");
    redirect("/feedback");
}


page("Bugs et suggestions");
?>

<article>
    <form method="post">
        <?= $v->render_validation() ?>
        <?= $description->render() ?>
        <button type="submit">Submit</button>
    </form>
</article>