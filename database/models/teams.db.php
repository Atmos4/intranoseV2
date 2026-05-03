<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity, Table(name: 'team_groups')]
class TeamGroup
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[ManyToOne]
    public Event|null $event = null;

    #[ManyToOne]
    public Activity|null $activity = null;

    #[Column]
    public string|null $name = null;

    #[Column(nullable: true)]
    public string|null $relay_format = null;

    /** @var Collection<int, Team> */
    #[OneToMany(targetEntity: Team::class, mappedBy: 'team_group', cascade: ["remove"])]
    public Collection $teams;

    #[Column]
    public bool $published = false;

    public function getRelayGroup(): ?RelayGroupDto
    {
        return $this->relay_format ? RelayFormatService::getGroup($this->relay_format) : null;
    }
}

#[Entity, Table(name: 'teams')]
class Team
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public string|null $name = null;

    #[Column(nullable: true)]
    public string|null $relay_format = null;

    #[Column(nullable: true)]
    public string|null $slot_order = null;

    #[ManyToOne]
    public TeamGroup|null $team_group = null;

    /** @var Collection<int, User> */
    #[ManyToMany(targetEntity: User::class)]
    public Collection $members;

    /** Get ordered members array with nulls for empty slots */
    public function getOrderedMembers(): array
    {
        if (!$this->slot_order) {
            return $this->members->toArray();
        }
        $ids = json_decode($this->slot_order, true) ?: [];
        $membersById = [];
        foreach ($this->members as $m) {
            $membersById[$m->id] = $m;
        }
        $ordered = [];
        foreach ($ids as $id) {
            $ordered[] = $id ? ($membersById[$id] ?? null) : null;
        }
        return $ordered;
    }

    public function getRelayFormat(): ?RelayFormatDto
    {
        return $this->relay_format ? RelayFormatService::get($this->relay_format) : null;
    }
}