<?php
class AuthService extends FactoryDependency
{
    function tryLogin(string $login, string $password, Validator &$v)
    {
        $user = User::getByLogin($login);
        if ($user) {
            switch (true) {
                case $user?->status == UserStatus::INACTIVE:
                    $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('PT15M'));
                    em()->persist($token);

                    $result = MailerFactory::createActivationEmail($user->real_email, $token->id)->send();

                    if ($result->success) {
                        logger()->info("Activation email sent to user {login}", ["login" => $user->login]);
                        $v->set_success("Un email a été envoyé à l'adresse " . MailHelper::obfuscate($user->real_email))
                            . ". Utilisez-le pour activer votre compte.";
                        em()->flush();
                    } else {
                        logger()->warning("Activation email failed to send for user {login}", ["login" => $user->login]);
                        $v->set_error($result->message);
                    }
                    return;
                case $user?->status == UserStatus::ACTIVE:
                    if (password_verify($password, $user->password)) {
                        logger()->info("Login successful for user {login}", ["login" => $user->login]);
                        $_SESSION['user_id'] = $user->id;
                        $_SESSION['user_permission'] = $user->permission;
                        redirect("/");
                        return;
                    }
                    logger()->info("Login failed for user {login}: wrong password", ["login" => $user->login]);
                    $v->set_error("Mauvais mot de passe");
                    return;
                case $user?->status == UserStatus::DEACTIVATED:
                    logger()->info("Login failed for user {login}: deactivated account", ["login" => $user->login]);
                    $v->set_error("Votre compte est bloqué. Contactez un administrateur.");
                    return;
                default:
                    break;
            }
        }
        logger()->info("Login failed: not found", ["login" => $login]);
        $v->set_error("Utilisateur non trouvé");
    }

    function createActivationLink(User $user): string
    {
        $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('PT15M'));
        em()->persist($token);
        em()->flush();
        return env("BASE_URL") . "/activation?token=$token->id";
    }
}