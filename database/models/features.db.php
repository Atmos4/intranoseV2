<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\JoinColumn;

#[Entity, Table(name: 'user_features')]
class UserFeature
{
    #[Id, Column]
    public string|null $featureName = null;

    #[Id, ManyToOne]
    public User $user;

    #[Column]
    public bool $enabled = false;

    function __construct(User $user, Feature $feature)
    {
        $this->featureName = $feature->value;
        $this->user = $user;
    }
}

/* Bit different logic here, feature is authorized if there is a line in ClubFeature table, and then can be enabled at club level. */
#[Entity, Table(name: 'club_features')]
class ClubFeature
{
    #[Id, Column]
    public string|null $featureName = null;

    #[Id, ManyToOne, JoinColumn(name: "club_slug", referencedColumnName: "slug")]
    public Club $club;

    #[Column]
    public bool $enabled = false;

    function __construct(Club $club, Feature $feature)
    {
        $this->featureName = $feature->value;
        $this->club = $club;
    }
}

enum Feature: string
{
    case Messages = "Messages";
    case Carpooling = "Carpooling";
    case JootForm = "JootForm";
    case Calendar = "Calendar";

    function on()
    {
        return has_feature($this);
    }

    function translate()
    {
        return match ($this) {
            Feature::Messages => "Messagerie",
            Feature::Carpooling => "Covoiturage",
            Feature::JootForm => "JotForm",
            Feature::Calendar => "Calendrier",
        };
    }
}