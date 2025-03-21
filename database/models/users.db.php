<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Uuid;

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
    public string $picture = "";

    #[Column]
    public UserStatus $status = UserStatus::INVALID;

    #[ManyToOne]
    public Family|null $family = null;

    #[Column]
    public bool $family_leader = false;

    /** @var Collection<int,AccessToken> entries */
    #[OneToMany(targetEntity: AccessToken::class, mappedBy: "user", cascade: ["remove"])]
    public Collection $tokens;

    /** @var Collection<int,EventEntry> entries */
    #[OneToMany(targetEntity: EventEntry::class, mappedBy: "user", cascade: ["remove"])]
    public Collection $event_entries;

    /** @var Collection<int,ActivityEntry> entries */
    #[OneToMany(targetEntity: ActivityEntry::class, mappedBy: "user", cascade: ["remove"])]
    public Collection $activity_entries;

    /** @var Collection<int,UserGroup> entries */
    #[ManyToMany(targetEntity: UserGroup::class, inversedBy: 'users')]
    public Collection $groups;

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

    function getPicture()
    {
        return self::getUserPicture($this->picture);
    }

    static function getUserPicture($file)
    {
        return $file && file_exists($file) ? "/" . $file : "/assets/images/none.jpg";
    }

    function replacePicture(string $newPicture)
    {
        $this->picture && file_exists($this->picture) && unlink($this->picture);
        $this->picture = $newPicture;
    }

    /** Get user by ID */
    static function get($user_id): User|null
    {
        return $user_id ? em()->find(User::class, $user_id) : null;
    }

    static function getControlledUserId(): int|null
    {
        if (isset($_SESSION['controlled_user_id'])) {
            Page::getInstance()->controlled();
        }
        return $_SESSION['controlled_user_id'] ?? null;
    }

    static function getMainUserId(): int|null
    {
        return $_SESSION['user_id'] ?? null;
    }

    static function getCurrentUserId(): int|null
    {
        return self::getControlledUserId() ?? self::getMainUserId();
    }

    static function getCurrent(): User|null
    {
        self::$currentUser ??= User::get(self::getCurrentUserId());
        return self::$currentUser;
    }

    static function getMain(): User|null
    {
        self::$mainUser ??= User::get(self::getMainUserId());
        return self::$mainUser;
    }

    static function existsWithLogin($login): bool
    {
        $logins = em()
            ->createQuery("SELECT u.login FROM User u WHERE u.login = :login")
            ->setParameter("login", $login)
            ->getArrayResult();
        return count($logins);
    }

    static function findAllByLogin($login): array
    {
        return em()
            ->createQuery("SELECT u.login FROM User u WHERE u.login LIKE :login")
            ->setParameter("login", $login . '%')
            ->getSingleColumnResult();
    }

    static function existsWithEmail($email): bool
    {
        $emails = em()
            ->createQuery('SELECT u.nose_email FROM User u WHERE u.nose_email = :email')
            ->setParameter('email', $email)
            ->getArrayResult();
        return count($emails);
    }

    static function findEmailWithPattern($email): array
    {
        return em()
            ->createQuery('SELECT u.nose_email FROM User u WHERE u.nose_email LIKE :email')
            ->setParameter('email', $email)
            ->getSingleColumnResult();
    }

    function hasFeature(Feature $f)
    {
        return has_feature($f, $this->id);
    }
}

enum UserStatus: string
{
    case INVALID = '';
    case DEACTIVATED = 'DEACTIVATED';
    case INACTIVE = 'INACTIVE';
    case ACTIVE = 'ACTIVE';
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
    public static $ROOT = [Permission::ROOT];
}

enum AccessTokenType: string
{
    case ACTIVATE = 'ACTIVATE';
    case INVITE = 'INVITE';
    case RESET_PASSWORD = 'RESET_PASSWORD';
    case REMEMBER_ME = 'REMEMBER_ME';
}

#[Entity, Table(name: 'access_tokens')]
class AccessToken
{
    #[Id]
    #[Column(length: 36, unique: true)]
    public string|null $id = null;

    #[ManyToOne]
    public User|null $user = null;

    #[Column(nullable: true)]
    public string|null $hashed_validator = null;

    #[Column]
    public DateTime $expiration;

    #[Column(length: 20)]
    public AccessTokenType $type;

    function __construct(User $user, AccessTokenType $type, DateInterval $duration = new DateInterval('PT5M'))
    {
        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->type = $type;
        $this->expiration = date_create()->add($duration);
    }

    /** This is added security against
     * - compromised DB with the hash
     * - timing attacks
     * Use this for long lived tokens */
    function createHashedValidator(): string
    {
        $validator = bin2hex(random_bytes(32));
        $this->hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
        return $validator;
    }

    static function retrieve(string $uuid, bool $forceExit = false): ?AccessToken
    {
        if (!Uuid::isValid($uuid)) {
            return $forceExit ? force_404("invalid token") : null;
        }
        // TODO: optimize with DQL if perf problems.
        $token = em()->find(AccessToken::class, $uuid);
        if (!$token) {
            return $forceExit ? force_404("token not found") : null;
        }
        if (date_create() > $token->expiration) {
            em()->remove($token);
            em()->flush();
            return $forceExit ? force_404("token expired") : null;
        }
        return $token;
    }

    static function retrieveOrFail(string $uuid): AccessToken
    {
        return self::retrieve($uuid, true) ?? throw new Exception("Token not found");
    }
}