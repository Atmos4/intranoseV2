<?php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\GeneratedValue;

/** A competition type (e.g. CFC, Relais-Sprint) grouping several relay formats. The id is a stable slug. */
#[Entity, Table(name: 'relay_groups')]
class RelayGroup
{
    #[Id, Column]
    public string $id;

    #[Column]
    public string $name;

    #[Column]
    public int $position = 0;
}

#[Entity, Table(name: 'relay_formats')]
class RelayFormat
{
    /** Stable slug, stored in Team::$relay_format */
    #[Id, Column]
    public string $id;

    #[Column]
    public string $name;

    #[ManyToOne]
    public RelayGroup|null $group = null;

    #[Column]
    public int $team_size = 0;

    /** @var string[] */
    #[Column(type: Types::JSON)]
    public array $rules = [];

    #[Column(nullable: true)]
    public string|null $description = null;

    /** If true, slot constraints are per-position (e.g. Relais-Sprint) */
    #[Column(name: 'ordered')]
    public bool $ordered = false;

    #[Column]
    public int $position = 0;

    /** @var Collection<int, RelaySlot> */
    #[OneToMany(targetEntity: RelaySlot::class, mappedBy: 'format', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[OrderBy(['position' => 'ASC'])]
    public Collection $slots;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    /** @return RelaySlot[] */
    public function getSlots(): array
    {
        return $this->slots->toArray();
    }

    /** @return int[] Leg durations in minutes per position, empty if the format defines none */
    public function getLegs(): array
    {
        $legs = array_map(fn(RelaySlot $s) => $s->leg_minutes, $this->getSlots());
        return array_filter($legs, fn($l) => $l !== null) ? $legs : [];
    }
}

#[Entity, Table(name: 'relay_slots')]
class RelaySlot
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[ManyToOne(inversedBy: 'slots')]
    public RelayFormat|null $format = null;

    #[Column]
    public int $position = 0;

    #[Column]
    public string $label;

    /** "H", "D" or null for any */
    #[Column(length: 1, nullable: true)]
    public string|null $sex = null;

    #[Column(nullable: true)]
    public int|null $min_category = null;

    #[Column(nullable: true)]
    public int|null $max_category = null;

    /** Leg duration in minutes */
    #[Column(nullable: true)]
    public int|null $leg_minutes = null;

    public function __construct(string $label = '', ?string $sex = null, ?int $min_category = null, ?int $max_category = null, ?int $leg_minutes = null)
    {
        $this->label = $label;
        $this->sex = $sex;
        $this->min_category = $min_category;
        $this->max_category = $max_category;
        $this->leg_minutes = $leg_minutes;
    }

    public function matches(?string $category): bool
    {
        if (!$category)
            return false;
        $parsed = RelayFormatService::parseCategory($category);
        if (!$parsed)
            return false;
        if ($this->sex !== null && $parsed['sex'] !== $this->sex)
            return false;
        if ($this->min_category !== null && $parsed['num'] < $this->min_category)
            return false;
        if ($this->max_category !== null && $parsed['num'] > $this->max_category)
            return false;
        return true;
    }

    public function toArray(): array
    {
        return ['label' => $this->label, 'sex' => $this->sex, 'min' => $this->min_category, 'max' => $this->max_category];
    }

    public function constraintText(): string
    {
        $parts = [];
        if ($this->sex)
            $parts[] = $this->sex;
        if ($this->min_category !== null) {
            if ($this->max_category !== null) {
                $parts[] = $this->min_category . '–' . $this->max_category;
            } else {
                $parts[] = $this->min_category . '+';
            }
        } elseif ($this->max_category !== null) {
            $parts[] = '≤' . $this->max_category;
        }
        return implode('', $parts);
    }
}
