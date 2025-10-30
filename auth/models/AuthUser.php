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

#[Entity, Table(name: 'auth_users')]
class AuthUser
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $email = "";

    #[Column]
    public string $password = "";

    #[ManyToMany(targetEntity: Club::class, inversedBy: 'users')]
    public Collection $clubs;

}

#[Entity, Table(name: 'auth_clubs')]
class Club
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;
    
    #[Column]
    public string $name = "";

    #[Column]
    public string $slug = "";

    #[ManyToMany(targetEntity: AuthUser::class, mappedBy: 'clubs')]
    public Collection $users;
}
