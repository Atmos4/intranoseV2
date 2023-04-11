<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'users')]
class User
{
    /** Current user singleton */
    private static User|null $currentUser = null;

    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $last_name = "";

    #[Column]
    public string $first_name = "";

    #[Column]
    public Gender $gender = Gender::M;

    #[Column]
    public int $licence = 0;

    #[Column]
    public int $sportident = 0;

    #[Column]
    public string $login = "";

    #[Column]
    public string $password = "";

    #[Column]
    public Permission $permission = Permission::USER;

    #[Column]
    public string $address = "";

    #[Column]
    public int $postal_code = 0;

    #[Column]
    public string $city = "";

    #[Column]
    public DateTime $birthdate;

    #[Column]
    public string $nose_email = "";

    #[Column]
    public string $real_email = "";

    #[Column]
    public string $phone = "";

    function __construct()
    {
        $this->birthdate = date_create();
    }

    function set_identity($last_name, $first_name, $licence, $gender)
    {
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        if ($licence)
            $this->licence = $licence;
        $this->gender = $gender;
    }

    function set_email($real_email, $nose_email)
    {
        $this->real_email = $real_email;
        $this->nose_email = $nose_email;
    }

    function set_perso($sportident, $address, $postal_code, $city, $phone)
    {
        $this->sportident = $sportident ?: 0;
        $this->address = $address;
        $this->postal_code = intval($postal_code);
        $this->city = $city;
        $normalized_phone = preg_replace('/[^0-9]/', '', $phone);
        $normalized_phone = substr($normalized_phone, -9);
        $final_phone = '+33' . $normalized_phone;
        $this->phone = $final_phone;
        var_dump($this->phone);
        var_dump($this->city);
    }

    function set_password($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    function set_login($login)
    {
        $this->login = $login;
    }

    static function getByLogin($login): User
    {
        $result = em()->getRepository(User::class)->findByLogin($login);
        return count($result) ? $result[0] : new User();
    }

    static function getCurrent(): User
    {
        if (isset($_SESSION['controlled_user_id'])) {
            Page::getInstance()->controlled();
        }
        self::$currentUser ??= em()->find(User::class, $_SESSION['controlled_user_id'] ?? $_SESSION['user_id']);
        return self::$currentUser;
    }

    static function getBySubstring($subString): array
    {
        // Returns all the numbers associated with a login $subString
        $user_numbers = em()
            ->createQuery("SELECT SUBSTRING(u.login, LENGTH(?1))
            FROM User u
            WHERE u.login LIKE ?1")
            ->setParameter(1, $subString . '%')
            ->getResult();


        $user_numbers = array_map(function ($value) {
            return $value[1] ? intval($value[1]) : null;
        }, $user_numbers);

        var_dump($user_numbers);
        return $user_numbers;
    }
}


enum Gender: string
{
    case M = 'M';
    case W = 'W';
}

enum Permission: string
{
    case USER = 'USER';
    case STAFF = 'STAFF';
    case COACH = 'COACH';
    case COACHSTAFF = 'COACHSTAFF';
    case GUEST = 'GUEST';
    case ROOT = 'ROOT';
}

class Access
{
    public static $EDIT_USERS = [Permission::COACHSTAFF, Permission::STAFF, Permission::ROOT];
    public static $ADD_EVENTS = [Permission::COACHSTAFF, Permission::STAFF, Permission::ROOT, Permission::COACH];
}