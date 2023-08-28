<?php
restrict_dev();
restrict_access();

$v = new Validator();
$address = $v->text("address")->placeholder("Destinataire")->required()->autocomplete("email");

if ($v->valid()) {
    $token = new AccessToken(User::getMain(), AccessTokenType::ACTIVATE, new DateInterval('PT30S'));
    em()->persist($token);
    em()->flush();

    $base_url = env("base_url");
    $subject = "Test token";
    $content = "Voici le lien pour tester le token: $base_url/activation?token=$token->id";

    $result = Mailer::create()
        ->to($address->value, $subject, $content)
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
    <button type="submit"><i class="fa fa-paper-plane"></i> Envoyer</button>
</form>