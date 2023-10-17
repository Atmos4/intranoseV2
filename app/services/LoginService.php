<?php
class AuthService
{
    static function tryLogin(string $login, string $password, Validator &$v)
    {
        $user = User::getByLogin($login);
        if (!$user) {
            $v->set_error("Utilisateur non trouvé");
            return;
        }
        switch (true) {
            case $user->status == UserStatus::INACTIVE:
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
                break;

            case $user->status == UserStatus::ACTIVE && password_verify($password, $user->password):
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_permission'] = $user->permission;
                redirect("/");
                break;
            case $user->status == UserStatus::DEACTIVATED:
                $v->set_error("Votre compte est bloqué. Contactez un administrateur.");
                break;
            default:
                $v->set_error("Utilisateur non trouvé");
                break;
        }
    }
}