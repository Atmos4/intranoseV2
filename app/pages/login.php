<?php
$v = new Validator();
$login = $v->text("login")->placeholder("Login")->required();
$password = $v->password("password")->placeholder("Password")->autocomplete("current-password")->required();
if ($v->valid()) {
    $user = User::getByLogin($login->value);
    if (!$user) {
        $login->set_error("Utilisateur non trouvé");
    } else if (!$user->active) {
        if (!$user->blocked) {
            $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('PT15M'));
            em()->persist($token);

            $result = MailerFactory::createActivationEmail($user->real_email, $token->id)->send();

            if ($result->success) {
                $v->set_success("Un email a été envoyé à l'adresse " . MailHelper::obfuscate($user->real_email))
                    . ". Utilisez-le pour activer votre compte.";
                em()->flush();
            } else {
                $v->set_error($result->message);
            }
        } else {
            $login->set_error("Votre compte est bloqué. Contactez un administrateur.");
        }
    } else if (password_verify($password->value, $user->password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_permission'] = $user->permission;
        redirect("/");
    } else {
        $login->set_error("Utilisateur non trouvé");
    }
}

page("Login")->css("login.css")->disableNav()->heading(false);
?>
<article>
    <form method="post">
        <h2 class="center">Intranose</h2>
        <?= $login->render() ?>
        <?= $password->render() ?>
        <?= $v->render_validation() ?>
        <button type="submit">Se connecter</button>
    </form>
    <a href="/reinitialiser-mot-de-passe">Mot de passe oublié ?</a>
</article>