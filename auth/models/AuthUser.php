<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Uuid;

enum AuthRole: string
{
    case USER = 'USER';
    case ROOT = 'ROOT';
}

#[Entity, Table(name: 'auth_users')]
class AuthUser
{
    #[Id]
    #[Column(length: 36, unique: true)]
    public ?string $id = null;

    #[Column]
    public string $login = "";

    #[Column]
    public string $email = "";

    #[Column]
    public string $password = "";

    #[Column]
    public AuthRole $role = AuthRole::USER;

    #[ManyToMany(targetEntity: AuthClub::class, inversedBy: 'users')]
    public Collection $clubs;

    public function __construct(string $login, string $email, string $password, AuthRole $permission)
    {
        $this->id = Uuid::uuid4();
        $this->login = $login;
        $this->email = $email;
        $this->password = $password;
        $this->permission = $permission;
    }

    public static function findByLogin(EntityManager $authEm, string $login): ?AuthUser
    {
        $r = $authEm
            ->createQuery('SELECT u FROM AuthUser u WHERE u.login = :login')
            ->setParameter('login', $login)->getResult();
        return count($r) ? $r[0] : null;
    }

    public static function findByEmail(EntityManager $authEm, string $email): ?AuthUser
    {
        $r = $authEm
            ->createQuery('SELECT u FROM AuthUser u WHERE u.email = :email')
            ->setParameter('email', $email)->getResult();
        return count($r) ? $r[0] : null;
    }
}

#[Entity, Table(name: 'auth_clubs')]
class AuthClub
{
    #[Id, Column, GeneratedValue]
    public ?int $id = null;

    #[Column]
    public string $name = "";

    #[Column]
    public string $slug = "";

    #[ManyToMany(targetEntity: AuthUser::class, mappedBy: 'clubs')]
    public Collection $users;
}
