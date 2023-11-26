<?php
restrict_dev();
restrict_access();

$v = new Validator();
$address = $v->text("address")->placeholder("Destinataire")->required()->autocomplete("email");
$subject = $v->text("subject")->placeholder("Object")->required();
$content = $v->textarea("content")->placeholder("Contenu")->required();

if ($v->valid()) {
    $token = new AccessToken(User::getMain(), AccessTokenType::ACTIVATE, new DateInterval('PT30S'));
    em()->persist($token);
    em()->flush();

    $result = Mailer::create()
        ->createEmail($address->value, $subject->value, $content->value)
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