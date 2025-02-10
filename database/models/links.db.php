<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'links')]
class Link
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $url = "";

    #[Column]
    public string $button_text = "";

    #[Column]
    public string $description = "";

    public function __construct($url, $button_text, $description)
    {
        $this->url = $url;
        $this->button_text = $button_text;
        $this->description = $description;
    }
}