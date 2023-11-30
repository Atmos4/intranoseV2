<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


#[Entity, Table(name: 'user_feedbacks')]
class UserFeedback
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[ManyToOne(targetEntity: User::class)]
    public User|null $user = null;

    #[Column(type: 'text')]
    public string $description = "";

}