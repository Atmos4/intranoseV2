<?php
restrict_access();
$user = User::getMain();
$v = new Validator();
$description = $v->textarea("description")->placeholder("Description")->required();

if ($v->valid()) {
    $new_feedback = new Feedback();
    $new_feedback->user = $user;
    $new_feedback->description = $description->value;
    em()->persist($new_feedback);
    em()->flush();
    Toast::success("Merci pour votre retour !");
    redirect("/feedbacks");
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