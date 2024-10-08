<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'shared_documents')]
class SharedFile
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string $name = "";

    #[Column]
    public string $path = "";

    #[Column]
    public DateTime $date;

    #[Column]
    public string $mime = "";

    #[Column]
    public Permission $permission_level = Permission::USER;

    #[ManyToOne]
    public Activity|null $activity = null;

    #[ManyToOne]
    public Event|null $event = null;

    function __construct()
    {
        $this->date = date_create();
    }

    function set(string $name, string $path, string $mime)
    {
        $this->name = $name;
        $this->path = $path;
        $this->mime = $mime;
    }

    /** Get document by ID */
    static function get($file_id): SharedFile|null
    {
        return em()->find(SharedFile::class, $file_id);
    }

    /** @return self[] */
    static function findBy($permission): array
    {
        return em()->getRepository(self::class)->findBy(['permission_level' => $permission]);
    }

}