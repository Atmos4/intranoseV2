<?php
class AuthService
{
    static function tryLogin(string $login, string $password, Validator &$v)
    {
        $user = User::getByLogin($login);
        switch (true) {
            case $user?->status == UserStatus::INACTIVE:
                $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('PT15M'));
                em()->persist($token);

                $result = MailerFactory::createActivationEmail($user->real_email, $token->id)->send();

                if ($result->success) {
                    logger()->info("User {$user->id} activation email sent");
                    $v->set_success("Un email a été envoyé à l'adresse " . MailHelper::obfuscate($user->real_email))
                        . ". Utilisez-le pour activer votre compte.";
                    em()->flush();
                } else {
                    logger()->warning("User {$user->id} activation email failed to send");
                    $v->set_error($result->message);
                }
                return;
            case $user?->status == UserStatus::ACTIVE && password_verify($password, $user->password):
                logger()->info("User {$user->id} logged in");
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_permission'] = $user->permission;
                redirect("/");
                return;
            case $user?->status == UserStatus::DEACTIVATED:
                logger()->info("User {$user->id} tried to log in but is blocked");
                $v->set_error("Votre compte est bloqué. Contactez un administrateur.");
                return;
            default:
                break;
        }
        logger()->info("User {$login} was not found");
        $v->set_error("Utilisateur non trouvé");
    }
}