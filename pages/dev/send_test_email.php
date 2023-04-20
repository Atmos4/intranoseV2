<?php
restrict_dev();

$v = new Validator();
$address = $v->text("address")->placeholder("Destinataire")->required()->autocomplete("email");
$subject = $v->text("subject")->placeholder("Objet")->required();
$content = $v->text("content")->placeholder("Contenu")->required();

if ($v->valid()) {
    $result = Mailer::create()
        ->to($address->value, $subject->value, $content->value)
        ->send();
    if ($result->success) {
        $v->set_success('Message has been sent');
    } else {
        $v->set_error($result->message);
    }
}

page("Test email") ?>
<form method="post">
    <?= $v->render_validation() ?>
    <?= $address->render() ?>
    <?= $subject->render() ?>
    <?= $content->render() ?>
    <button type="submit"><i class="fa fa-paper-plane"></i> Envoyer</button>
</form>