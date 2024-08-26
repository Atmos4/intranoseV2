<?php

use Doctrine\ORM\EntityManager;

class UserService
{
    static function getByLogin(EntityManager $em, $login): User|null
    {
        $result = $em->getRepository(User::class)->findByLogin($login);
        return count($result) ? $result[0] : null;
    }

    /** @return User[] */
    static function getActiveUserList()
    {
        return em()->createQuery("SELECT u FROM User u WHERE u.status != :status ORDER BY u.last_name ASC, u.first_name ASC")
            ->setParameters(['status' => UserStatus::DEACTIVATED])->getResult();
    }


    /** @return DeactivatedUserDto[] */
    static function getDeactivatedUserList()
    {
        return em()->createQuery("SELECT NEW DeactivatedUserDto(u.id, u.last_name, u.first_name) FROM User u WHERE u.status = :status ORDER BY u.last_name ASC, u.first_name ASC")
            ->setParameters(['status' => UserStatus::DEACTIVATED])->getResult();
    }

    static function countUsersWithSameEmail($email)
    {
        return em()->createQuery("SELECT COUNT(u) FROM User u WHERE u.real_email = :email")
            ->setParameters(['email' => $email])->getSingleScalarResult();
    }

    /** @param User[] $users
     * @return bool true if successful, false otherwise
     */
    static function reactivateUsers(array $users): bool
    {
        foreach ($users as $user) {
            $user->status = UserStatus::INACTIVE;
            logger()->info(
                "User reactivated",
                ["login" => $user->login, "adminUserId" => User::getMainUserId()]
            );
            Toast::success("$user->first_name réactivé");
        }
        em()->flush();
        return true;
    }

    static function deactivateUser(User $user)
    {
        $user->status = UserStatus::DEACTIVATED;
        em()->flush();
        Toast::success("Utilisateur désactivé");
        redirect("/licencies/desactive");
        return true;
    }
}

class UserHelper
{
    private static function sanitizeName(...$name): array
    {
        $result = [];
        foreach ($name as $str) {
            $str = strtolower(trim($str));
            // Remove whitespace and replace with hyphens
            $str = str_replace(' ', '-', $str);
            // Transliterate accented characters to their non-accented versions
            $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
            // Remove any remaining non-alphanumeric characters (except hyphen)
            $str = preg_replace('/[^a-zA-Z0-9-]/', '', $str);
            $result[] = $str;
        }
        return $result;
    }

    static function generateUserEmail($firstName, $lastName)
    {
        [$firstName, $lastName] = self::sanitizeName($firstName, $lastName);
        $nose_email = "$firstName.$lastName@nose42.fr";

        if (!User::existsWithEmail($nose_email)) {
            return $nose_email;
        }

        // generate email with increment -- should almost never happen
        $emails = User::findEmailWithPattern("$firstName.$lastName%");
        $increment = 1;
        while ($increment < 10) {
            $newEmail = "$firstName.$lastName" . "$increment@nose42.fr";
            if (!in_array($newEmail, $emails)) {
                return $newEmail;
            }
            $increment++;
        }

        throw new UserCreationException("Couldnt create email");
    }

    static function generateUserLogin($firstName, $lastName)
    {
        [$firstName, $lastName] = self::sanitizeName($firstName, $lastName);
        $login = $lastName . "_" . substr($firstName, 0, 1);
        if (!User::existsWithLogin($login)) {
            return $login;
        }

        // generate login with increment
        $loginList = User::findAllByLogin($login);
        $increment = 1;
        while ($increment < 10) { // Hopefully we don't have 10 users with the same login
            $newLogin = $login . $increment;
            if (!in_array($newLogin, $loginList)) {
                return $newLogin;
            }
            $increment++;
        }

        throw new UserCreationException("Couldnt create login");
    }
}

// DTOs
class DeactivatedUserDto
{
    function __construct(
        public int $id,
        public string $last_name,
        public string $first_name,
    ) {

    }
}