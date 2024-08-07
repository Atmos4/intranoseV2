<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'notifications_subscriptions')]
class NotificationSubscription
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;
    #[Column]
    public string $endpoint = "";
    #[Column]
    public string $p256dh = "";
    #[Column]
    public string $auth = "";
}