<?php

$v = new Validator();
$address = $v->email("address")->placeholder("Email")->required()->autocomplete("email");

if ($v->valid()) {
    $user = em()->getRepository(User::class)->findOneBy(['real_email' => $address->value]);
    if ($user) {
        $token = new AccessToken($user, AccessTokenType::RESET_PASSWORD, new DateInterval('PT2H'));
        em()->persist($token);

        $base_url = env("BASE_URL");
        $subject = "Réinitialisation du mot de passe";
        $content = "Voici le lien pour réinitialiser votre mot de passe: $base_url/nouveau-mot-de-passe?token=$token->id";

        $result = Mailer::create()
            ->to($address->value, $subject, $content)
            ->send();
        if ($result->success) {
            $v->set_success('Message has been sent');
            em()->flush();
        } else {
            $v->set_error($result->message);
        }
    } else {
        $v->set_error("Utilisateur non trouvé");
    }
}

page("Réinitialisation du mot de passe")->disableNav()->heading(false);
?>
<nav id="page-actions">
    <a href="/login" class="secondary">
        <i class="fa fa-caret-left"></i> Retour</a>
</nav>

<article>
    <form method="post">
        <h2 class="center">Réinitialisation du mot de passe</h2>
        <?= $v->render_validation() ?>
        <?= $address->render() ?>
        <button type="submit"><i class="fa fa-paper-plane"></i> Envoyer</button>
    </form>
</article>