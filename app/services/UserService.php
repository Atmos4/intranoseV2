<?php
class UserService
{

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