<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: UserRepository::class), Table(name: 'users')]
class User
{
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
        $this->sportident = $sportident;
        $this->address = $address;
        $this->postal_code = $postal_code;
        $this->city = $city;
        $this->$phone = $phone;
    }

    function set_password($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    function set_login($login)
    {
        $this->login = $login;
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

class UserRepository extends EntityRepository
{
    function getByLogin($login): User
    {
        $result = $this->findByLogin($login);
        return count($result) ? $result[0] : new User();
    }
}