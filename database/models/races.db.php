<?php
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'race_entries')]
class RaceEntry
{
    #[Id, ManyToOne(targetEntity: User::class)]
    public User|null $user = null;

    #[Id, ManyToOne(targetEntity: Race::class)]
    public Race|null $race = null;

    #[Column]
    public bool $present = false;

    #[Column]
    public bool $upgraded = false;

    #[Column]
    public int $licence = 0;

    #[Column]
    public int $sport_ident = 0;

    #[Column]
    public string $comment = "";

// private function exists_in_db(): bool
// {
//     $existing = fetch("SELECT present FROM inscriptions_courses WHERE id_course=? AND id_runner = ?", $this->race_id, $this->user_id);
//     return !!count($existing);
// }

// function save_in_db()
// {
//     if ($this->exists_in_db()) {
//         query_db(
//             "UPDATE inscriptions_courses SET present=?,surclasse=?, licence=?, si=?, id_cat=?, rmq=? WHERE id_course=? AND id_runner=?",
//             $this->present,
//             $this->upgraded,
//             $this->licence,
//             $this->sport_ident,
//             0,
//             $this->comment,
//             $this->race_id,
//             $this->user_id
//         );
//     } else {
//         query_db(
//             "INSERT INTO inscriptions_courses(id_course, id_runner, present,surclasse, licence, si, id_cat, rmq) VALUES(?,?,?,?,?,?,?,?)",
//             $this->race_id,
//             $this->user_id,
//             $this->present,
//             $this->upgraded,
//             $this->licence,
//             $this->sport_ident,
//             0,
//             $this->comment
//         );
//     }
// }
}

#[Entity, Table(name: 'races')]
class Race
{
    #[Id, Column, GeneratedValue]
    public int|null $id = null;

    #[Column]
    public DateTime $date;

    #[Column]
    public string $name;

    #[Column]
    public string $place;

    #[ManyToOne(targetEntity: Event::class, inversedBy: "races")]
    public Event|null $event = null;

    #[OneToMany(targetEntity: RaceEntry::class, mappedBy: "race", cascade: ["remove"])]
    public Collection $entries;

    function set_values(string $name, DateTime $date, string $place, Event $event)
    {
        $this->name = $name;
        $this->place = $place;
        $this->date = $date;
        $this->event = $event;
    }
}