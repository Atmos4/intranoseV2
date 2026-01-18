<?php

use Doctrine\ORM\EntityManager;

class AuthService extends FactoryDependency
{
    private const REMEMBERME_COOKIE = 'rememberme';

    function __construct(private EntityManager $em)
    {
    }

    function tryLogin(string $login, string $password, bool $rememberMe = false, Validator &$v = null): bool
    {
        $v ??= new Validator;
        if (AuthService::tryMatchUserPassword(UserService::getByLogin($this->em, $login), $password, $rememberMe, $v)) {
            return true;
        }
        if (AuthService::tryMatchUserPassword(UserService::getByEmail($this->em, $login), $password, $rememberMe, $v)) {
          return true;
        }
        logger()->info("Login failed: not found", ["login" => $login]);
        $v->set_error("Utilisateur non trouvé");
        return false;
    }

    private function tryMatchUserPassword(User|null $user, string $password, bool $rememberMe = false, Validator &$v = null): bool{

        if (!$user) {
            return false;
        }
        switch ($user->status) {
            case UserStatus::INACTIVE:
                $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('PT15M'));
                $this->em->persist($token);

                $result = MailerFactory::createActivationEmail($user->real_email, $token->id)->send();

                if ($result->success) {
                    logger()->info("Activation email sent to user {login}", ["login" => $user->login]);
                    $v->set_success("Un email a été envoyé à l'adresse " . MailHelper::obfuscate($user->real_email))
                        . ". Utilisez-le pour activer votre compte.";
                    $this->em->flush();
                    return false;
                } else {
                    logger()->warning("Activation email failed to send for user {login}", ["login" => $user->login]);
                    $v->set_error($result->message);
                }
                return false;
            case UserStatus::ACTIVE:
                if (password_verify($password, $user->password)) {
                    logger()->info("Login successful for user {login}", ["login" => $user->login]);
                    $this->loginUserSession($user);
                    $user->last_connection = date_create();
                    if ($rememberMe) {
                        $this->createRememberMeToken($user);
                    }

                    $this->em->flush();

                    if (isset($_SESSION["deep_url"])) {
                        redirect($_SESSION["deep_url"]);
                        unset($_SESSION["deep_url"]);
                        return false;
                    }
                    return true;
                }
                logger()->info("Login failed for user {login}: wrong password", ["login" => $user->login]);
                $v->set_error("Mauvais mot de passe");
                return false;
            case UserStatus::DEACTIVATED:
                logger()->info("Login failed for user {login}: deactivated account", ["login" => $user->login]);
                $v->set_error("Votre compte est bloqué. Contactez un administrateur.");
                return false;
            default:
                break;
        }
        return false;
    }

    private function loginUserSession(User $user)
    {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_permission'] = $user->permission;
    }

    function logout()
    {
        // Remove session data.
        $this->deleteUserTokens($_SESSION['user_id']);
        self::destroySession();
    }

    static function destroySession()
    {
        $_SESSION = [];
        setcookie(self::REMEMBERME_COOKIE, '', -1);
        session_destroy();
    }

    function createActivationLink(User $user): string
    {
        $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('PT15M'));
        $this->em->persist($token);
        $this->em->flush();
        return env("BASE_URL") . "/activation?token=$token->id";
    }

    function createRememberMeToken(User $user)
    {
        // Delete user tokens
        // AP 2024-04 - commenting this for now. Let's see if this poses a security issue.
        //$this->deleteUserTokens($user->id);

        $token = new AccessToken($user, AccessTokenType::REMEMBER_ME, new DateInterval('P1M')); // 1month
        $validator = $token->createHashedValidator();
        $this->em->persist($token);

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
        $this->em?->createQueryBuilder()
            ->delete(AccessToken::class, 'a')
            ->where('a.user = :user_id')
            ->setParameter("user_id", $userId)
            ->getQuery()->execute();
    }

    function isUserLoggedIn()
    {
        if (isset($_SESSION['user_permission'])) {
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

        $token->user->last_connection = date_create();
        $this->em->flush();

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
