<?php
class AuthService extends FactoryDependency
{
    private const REMEMBERME_COOKIE = 'rememberme';

    function tryLogin(string $login, string $password, bool|null $rememberMe, Validator &$v)
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
                        $this->loginUserSession($user);
                        if ($rememberMe) {
                            $this->createRememberMeToken($user);
                        }

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

    private function loginUserSession(User $user)
    {
        // prevent session fixation/hijack
        session_regenerate_id();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_permission'] = $user->permission;
    }

    function logout()
    {
        // Remove session data.
        $this->deleteUserTokens($_SESSION['user_id']);
        $_SESSION = [];
        setcookie(self::REMEMBERME_COOKIE, '', -1);
        session_destroy();
    }

    function createActivationLink(User $user): string
    {
        $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('PT15M'));
        em()->persist($token);
        em()->flush();
        return env("BASE_URL") . "/activation?token=$token->id";
    }

    function createRememberMeToken(User $user)
    {
        // Dele
        $this->deleteUserTokens($user->id);

        $token = new AccessToken($user, AccessTokenType::REMEMBER_ME, new DateInterval('P1M')); // 1month
        $validator = $token->createHashedValidator();
        em()->persist($token);
        em()->flush();

        setcookie(
            self::REMEMBERME_COOKIE,
            "$token->id:$validator",
            $token->expiration->getTimestamp(),
            httponly: true
        );
    }

    /** Always delete all user tokens when invalidating. 
     * If you want more custom behavior, write another function. But maybe you shouldn't. */
    function deleteUserTokens(string $userId)
    {
        em()->createQueryBuilder()
            ->delete(AccessToken::class, 'a')
            ->where('a.user = :user_id')
            ->setParameter("user_id", $userId)
            ->getQuery()->execute();
    }

    function isUserLoggedIn()
    {
        if (isset ($_SESSION['user_permission'])) {
            return true;
        }

        $sessionString = filter_input(INPUT_COOKIE, self::REMEMBERME_COOKIE, FILTER_SANITIZE_STRING);
        if (!$sessionString)
            return false;

        [$selector, $validator] = self::parseSessionToken($sessionString);
        $token = AccessToken::retrieve($selector);
        if (!$token || $token->type !== AccessTokenType::REMEMBER_ME || !$token->hashed_validator)
            return false;

        // AP 2024-03: we may want to throw an alert here. If this password_verify fails, it is very concerning.
        if (!password_verify($validator, $token->hashed_validator))
            return false;

        // At this point, the user has been verified and can be logged in!
        logger()->info("User {userId} logged in with long-lived session token", ["userId" => $token->user->id]);
        $this->loginUserSession($token->user);
        return true;
    }

    private static function parseSessionToken(string $token): ?array
    {
        $parts = explode(':', $token);
        if ($parts && count($parts) == 2) {
            return [$parts[0], $parts[1]];
        }
        return null;
    }

}