<?php

$v = new Validator();
$login = $v->text("login")->placeholder("Login")->required()->autocomplete("username");

if ($v->valid()) {
    $user = em()->getRepository(User::class)->findOneBy(['login' => $login->value]);
    if ($user?->status == UserStatus::ACTIVE) {
        $token = new AccessToken($user, AccessTokenType::RESET_PASSWORD, new DateInterval('PT15M'));
        em()->persist($token);

        $base_url = env("BASE_URL");
        $subject = "Réinitialisation du mot de passe";
        $content = "Voici le lien pour réinitialiser votre mot de passe: $base_url/nouveau-mot-de-passe?token=$token->id";

        $result = Mailer::create()
            ->createEmail($user->real_email, $subject, $content)
            ->send();
        if ($result->success) {
            logger()->info("User {login} password reset email sent", ["login" => $user->login]);
            $v->set_success("Mail envoyé à l'adresse " . MailHelper::obfuscate($user->real_email));
            em()->flush();
        } else {
            logger()->warning("User {login} password reset email failed to send", ["login" => $user->login]);
            $v->set_error($result->message);
        }
    } else {
        $v->set_error("Utilisateur non trouvé");
    }
}

page("Réinitialisation du mot de passe")->disableNav()->heading(false);
?>

<?= actions()->back("/login") ?>
<article>
    <form method="post">
        <h2 class="center">Réinitialisation du mot de passe</h2>
        <?= $v->render_validation() ?>
        <p>Entrez votre login pour recevoir un email de réinitialisation de votre mot de passe. Le login a
            habituellement comme format <code>(nom de famille en miniscule)_(première lettre du prénom)</code>.
        </p>
        <?= $login->render() ?>
        <button type="submit"><i class="fa fa-paper-plane"></i> Envoyer</button>
    </form>
</article>