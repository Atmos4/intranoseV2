<?php
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity, Table(name: 'clubs')]
class Club
{
    #[Id, Column(unique: true)]
    public string $slug; // intranose

    #[Column]
    public string $name; // Intranose

    #[Column(nullable: true)]
    public string|null $google_calendar_id = "";

    #[Column(nullable: true)]
    public string|null $google_credential_path = "";

    #[Column(options: ["default" => "green"])]
    public ThemeColor $themeColor;
    function __construct($name, $slug)
    {
        $this->name = $name;
        $this->slug = $slug;
        $this->themeColor = ThemeColor::green;
    }

    function toForm()
    {
        return [
            "name" => $this->name,
            "slug" => $this->slug,
            "color" => $this->themeColor->value
        ];
    }
}

enum ThemeColor: string
{
    case fuchsia = "fuchsia";
    case green = "green";
    case grey = "grey";
    case indigo = "indigo";
    case jade = "jade";
    case lime = "lime";
    case orange = "orange";
    case pink = "pink";
    case pumpkin = "pumpkin";
    case purple = "purple";
    case red = "red";
    case sand = "sand";
    case slate = "slate";
    case violet = "violet";
    case yellow = "yellow";
    case zinc = "zinc";

    public function getColorCode(): string
    {
        return match ($this) {
            self::fuchsia => '#c1208b',
            self::green => '#398712',
            self::grey => '#ababab',
            self::indigo => '#524ed2',
            self::jade => '#007a50',
            self::lime => '#a5d601',
            self::orange => '#d24317',
            self::pink => '#d92662',
            self::pumpkin => '#ff9500',
            self::purple => '#9236a4',
            self::red => '#c52f21',
            self::sand => '#ccc6b4',
            self::slate => '#525f7a',
            self::violet => '#7540bf',
            self::yellow => '#f2df0d',
            self::zinc => '#646b79',
        };
    }

    static function colorsList(): array
    {
        return array_reduce(ThemeColor::cases(), function ($carry, $c) {
            $carry[$c->value] = $c->getColorCode();
            return $carry;
        }, []);
    }

    function translate(): string
    {
        return match ($this) {
            self::fuchsia => "Fuchsia",
            self::green => "Vert",
            self::grey => "Gris",
            self::indigo => "Indigo",
            self::jade => "Jade",
            self::lime => "Citron vert",
            self::orange => "Orange",
            self::pink => "Rose",
            self::pumpkin => "Citrouille",
            self::purple => "Violet",
            self::red => "Rouge",
            self::sand => "Sable",
            self::slate => "Ardoise",
            self::violet => "Violet",
            self::yellow => "Jaune",
            self::zinc => "Zinc",
        };
    }
}