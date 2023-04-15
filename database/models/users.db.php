<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'users')]
class User
{
    /** Current user singleton */
    private static User|null $currentUser = null;

    /** Main user singleton */
    private static User|null $mainUser = null;

    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $last_name = "";

    #[Column]
    public string $first_name = "";

    #[Column]
    public Gender $gender = Gender::M;

    #[Column]
    public string $login = "";

    #[Column]
    public string $password = "";

    #[Column]
    public Permission $permission = Permission::USER;

    #[Column]
    public DateTime $birthdate;

    #[Column]
    public string $nose_email = "";

    #[Column]
    public string $real_email = "";

    #[Column]
    public string $phone = "";

    #[Column]
    public bool $active = false;

    #[ManyToOne]
    public Family|null $family = null;

    #[Column]
    public bool $family_leader = false;

    function __construct()
    {
        $this->birthdate = date_create();
    }

    function set_identity($last_name, $first_name, $gender)
    {
        $this->last_name = $last_name;
        $this->first_name = $first_name;
        $this->gender = $gender;
    }

    function set_email($real_email, $nose_email)
    {
        $this->real_email = $real_email;
        $this->nose_email = $nose_email;
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

    static function get($user_id): User
    {
        return em()->find(User::class, $user_id);
    }

    static function getCurrent(): User
    {
        if (isset($_SESSION['controlled_user_id'])) {
            Page::getInstance()->controlled();
        }
        self::$currentUser ??= em()->find(User::class, $_SESSION['controlled_user_id'] ?? $_SESSION['user_id']);
        return self::$currentUser;
    }

    static function getMain(): User
    {
        self::$mainUser ??= em()->find(User::class, $_SESSION['user_id']);
        return self::$mainUser;
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

        return $user_numbers;
    }

    static function findByFirstAndLastName($firstname, $lastname)
    {
        $query = em()->createQuery('SELECT u FROM User u WHERE u.first_name = :firstname AND u.last_name = :lastname')
            ->setParameter('firstname', $firstname)
            ->setParameter('lastname', $lastname);

        return $query->getResult();
    }
}

#[Entity, Table(name: 'families')]
class Family
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $name = "";

    /** @var Collection<int, User> members */
    #[OneToMany(targetEntity: User::class, mappedBy: 'family')]
    public Collection $members;

    function __construct()
    {
        $this->members = new ArrayCollection();
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