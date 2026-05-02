<?php

class RelaySlotDto
{
    public function __construct(
        public string $label,
        public ?string $sex = null,
        public ?int $min_category = null,
        public ?int $max_category = null,
    ) {
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

class RelayGroupDto
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}

class RelayFormatDto
{
    public function __construct(
        public string $id,
        public string $name,
        public string $group_id,
        public string $group,
        public int $team_size,
        public array $rules = [],
        public ?string $description = null,
        /** @var RelaySlotDto[] */
        public array $slots = [],
        /** @var int[] Leg durations in minutes per position */
        public array $legs = [],
        /** If true, slot constraints are per-position (e.g. Relais-Sprint) */
        public bool $ordered = false,
    ) {
    }

    /** @return RelaySlotDto[] - explicit slots or auto-generated */
    public function getSlots(): array
    {
        return !empty($this->slots) ? $this->slots : RelayFormatService::autoSlots($this);
    }

    /** @return int[] - explicit legs or auto-generated equal splits */
    public function getLegs(): array
    {
        return !empty($this->legs) ? $this->legs : RelayFormatService::autoLegs($this);
    }
}

class RelayFormatService
{
    /** @return RelayGroupDto[] */
    public static function groups(): array
    {
        return [
            new RelayGroupDto("relais_cat", "Relais par catégorie"),
            new RelayGroupDto("cfc", "Championnat de France des Clubs"),
            new RelayGroupDto("relais_sprint", "Relais-Sprint"),
            new RelayGroupDto("cne", "Critérium National des Équipes"),
            new RelayGroupDto("trophees", "Trophées"),
            new RelayGroupDto("relais_couleur", "Relais de couleur"),
        ];
    }

    public static function getGroup(string $id): ?RelayGroupDto
    {
        foreach (self::groups() as $group) {
            if ($group->id === $id)
                return $group;
        }
        return null;
    }

    /** @return array<string, string> for select options */
    public static function groupOptions(): array
    {
        $options = ["" => "— Aucun —"];
        foreach (self::groups() as $g) {
            $options[$g->id] = $g->name;
        }
        return $options;
    }

    public static function get(string $id): ?RelayFormatDto
    {
        foreach (self::all() as $format) {
            if ($format->id === $id)
                return $format;
        }
        return null;
    }

    /** @return RelayFormatDto[] */
    public static function byGroup(string $groupId): array
    {
        return array_values(array_filter(self::all(), fn($f) => $f->group_id === $groupId));
    }

    /** @return array<string, string> for select options, filtered by group */
    public static function formatOptions(?string $groupId = null): array
    {
        $options = ["" => "— Aucun —"];
        $formats = $groupId ? self::byGroup($groupId) : self::all();
        foreach ($formats as $f) {
            $options[$f->id] = $f->name . " ({$f->team_size}p)";
        }
        return $options;
    }

    /** @return array{sex: string, num: int}|null */
    public static function parseCategory(string $category): ?array
    {
        if (preg_match('/^([DH])(\d+)/i', trim($category), $m)) {
            return ['sex' => strtoupper($m[1]), 'num' => intval($m[2])];
        }
        return null;
    }

    /**
     * Map a raw age to the FFCO category bracket number.
     * 10,12,14,16,18,20 (every 2 years), 21 (ages 21–34), then 35,40,45,50,... (every 5 years).
     */
    public static function categoryBracket(int $age): int
    {
        if ($age < 10)
            return 10;
        if ($age <= 20)
            return $age - ($age % 2);  // 10,12,14,16,18,20
        if ($age < 35)
            return 21;                   // 21–34 → 21
        return $age - ($age % 5);                   // 35,40,45,50,...
    }

    /**
     * Compute the FFCO-style category string from a user's birthdate and gender.
     * Age as of Dec 31 of the event year, mapped to official bracket.
     * Gender M → "H", W → "D".
     * @return string e.g. "H21", "D16"
     */
    public static function computeCategory(User $user, DateTime $eventDate): string
    {
        $year = (int) $eventDate->format('Y');
        $dec31 = new DateTime("$year-12-31");
        $age = (int) $user->birthdate->diff($dec31)->y;
        $sex = $user->gender === Gender::M ? 'H' : 'D';
        return $sex . self::categoryBracket($age);
    }

    /** @return RelaySlotDto[] */
    public static function simpleSlots(int $count, ?string $sex = null, ?int $min = null, ?int $max = null): array
    {
        $slots = [];
        for ($i = 0; $i < $count; $i++) {
            $slots[] = new RelaySlotDto("Relayeur " . ($i + 1), $sex, $min, $max);
        }
        return $slots;
    }

    /** @return int[] - empty for formats without explicit leg durations */
    public static function autoLegs(RelayFormatDto $f): array
    {
        return [];
    }

    /** @return RelaySlotDto[] - auto-generate slots for simple formats */
    public static function autoSlots(RelayFormatDto $f): array
    {
        return match ($f->id) {
            'relais_cat_d12' => self::simpleSlots(2, null, null, 12),
            'relais_cat_d16' => self::simpleSlots(3, null, 14, 16),
            'relais_cat_d20' => self::simpleSlots(3, null, 16, 20),
            'relais_cat_d21' => self::simpleSlots(3, null, 18),
            'relais_cat_d35' => self::simpleSlots(3, null, 35),
            'relais_cat_d45' => self::simpleSlots(3, null, 45),
            'relais_cat_d55' => self::simpleSlots(2, null, 55),
            'relais_cat_d65' => self::simpleSlots(2, null, 65),
            'relais_cat_d75' => self::simpleSlots(2, null, 75),
            default => self::simpleSlots($f->team_size),
        };
    }

    /**
     * Validate team composition against slots.
     * @param RelaySlotDto[] $slots
     * @param string[] $categories - member category strings (e.g. ["H21", "D16", ...])
     * @return array{filled: int, total: int, slots: array} - each slot => matched category or null
     */
    public static function validateComposition(array $slots, array $categories): array
    {
        $available = $categories;
        // Sort slot indices by specificity (most constrained first)
        $indices = array_keys($slots);
        usort($indices, function ($a, $b) use ($slots) {
            return self::slotSpecificity($slots[$b]) - self::slotSpecificity($slots[$a]);
        });

        $assignments = [];
        foreach ($indices as $i) {
            $assignments[$i] = null;
            foreach ($available as $k => $cat) {
                if ($slots[$i]->matches($cat)) {
                    $assignments[$i] = $cat;
                    unset($available[$k]);
                    break;
                }
            }
        }
        ksort($assignments);

        $filled = count(array_filter($assignments, fn($v) => $v !== null));
        $extras = array_values(array_filter($available));
        return ['filled' => $filled, 'total' => count($slots), 'slots' => $assignments, 'extras' => $extras];
    }

    private static function slotSpecificity(RelaySlotDto $slot): int
    {
        $s = 0;
        if ($slot->sex !== null)
            $s += 4;
        if ($slot->max_category !== null)
            $s += 2;
        if ($slot->min_category !== null)
            $s += 1;
        return $s;
    }

    /**
     * Parse POST data for a team index and resolve members with their categories.
     * Shared by _slots_component.php and _composition_component.php to avoid duplication.
     *
     * @return array{
     *   team_relay_format: string,
     *   current_format: ?RelayFormatDto,
     *   slot_defs: RelaySlotDto[],
     *   is_ordered: bool,
     *   team_members: array,
     *   member_categories: string[]
     * }
     */
    public static function resolveTeamContext(int|string $team_index, TeamGroup $team_group): array
    {
        $team_relay_format = $_POST["team_{$team_index}_relay_format"] ?? $_GET["relay_format"] ?? "";

        $member_ids_raw = $_POST["team_{$team_index}_members"] ?? [];
        $member_ids = is_string($member_ids_raw) ? json_decode($member_ids_raw, true) : $member_ids_raw;
        if (!is_array($member_ids))
            $member_ids = [];

        $team_members = [];
        $member_categories = [];

        foreach ($member_ids as $member_id) {
            if ($member_id) {
                $user = em()->find(User::class, intval($member_id));
                if ($user) {
                    $category = self::computeCategory($user, $team_group->event->start_date);
                    $team_members[] = [
                        'id' => $user->id,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'picture' => $user->getPicture(),
                        'category' => $category,
                    ];
                    if ($category)
                        $member_categories[] = $category;
                } else {
                    $team_members[] = null;
                }
            } else {
                $team_members[] = null;
            }
        }

        $current_format = $team_relay_format ? self::get($team_relay_format) : null;
        $slot_defs = $current_format ? $current_format->getSlots() : [];
        $is_ordered = $current_format ? $current_format->ordered : false;

        return compact('team_relay_format', 'current_format', 'slot_defs', 'is_ordered', 'team_members', 'member_categories');
    }

    /** @return RelayFormatDto[] */
    public static function all(): array
    {
        return [
            // ===== Relais par catégorie (Article 8) =====
            new RelayFormatDto(
                id: "relais_cat_d12",
                name: "D12 / H12",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 2,
                rules: ["2 coureurs D/H12"],
                description: "Temps total : 40 min. Niveau bleu.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d16",
                name: "D16 / H16 (Dames : 2, Hommes : 3)",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 3,
                rules: ["Dames : 2 coureurs D/H14–D/H16", "Hommes : 3 coureurs D/H14–D/H16"],
                description: "Temps total : 60 min (D) / 90 min (H). Niveau jaune.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d20",
                name: "D20 / H20 (Dames : 2, Hommes : 3)",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 3,
                rules: ["Dames : 2 coureurs D/H16–D/H20", "Hommes : 3 coureurs D/H16–D/H20"],
                description: "Temps total : 70 min (D) / 105 min (H). Niveau violet.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d21",
                name: "D21 / H21",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 3,
                rules: ["3 coureurs D/H18 et +"],
                description: "Temps total : 120 min. Niveau violet.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d35",
                name: "D35 / H35",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 3,
                rules: ["3 coureurs D/H35 et +"],
                description: "Temps total : 105 min. Niveau violet.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d45",
                name: "D45 / H45",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 3,
                rules: ["3 coureurs D/H45 et +"],
                description: "Temps total : 90 min. Niveau violet.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d55",
                name: "D55 / H55",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 2,
                rules: ["2 coureurs D/H55 et +"],
                description: "Temps total : 60 min. Niveau violet.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d65",
                name: "D65 / H65",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 2,
                rules: ["2 coureurs D/H65 et +"],
                description: "Temps total : 60 min. Niveau violet.",
            ),
            new RelayFormatDto(
                id: "relais_cat_d75",
                name: "D75 / H75",
                group_id: "relais_cat",
                group: "Relais par catégorie",
                team_size: 2,
                rules: ["2 coureurs D/H75 et +"],
                description: "Temps total : 60 min. Niveau violet.",
            ),

            // ===== CFC — Championnat de France des Clubs (Article 9) =====
            new RelayFormatDto(
                id: "cfc_n1",
                name: "Nationale 1 (8 coureurs)",
                group_id: "cfc",
                group: "CFC",
                team_size: 8,
                rules: [
                    "1 jeune Homme (H14 à H18)",
                    "1 jeune Dame (D14 à D18)",
                    "1 H35 et +",
                    "1 D35 et +",
                    "2 Dames (D16 et +)",
                    "2 Hommes (H16 et +)",
                ],
                description: "Parcours : 50′, 30′, 20′, 50′, 30′, 20′, 30′, 50′. Total : 280 min.",
                slots: [
                    new RelaySlotDto("Jeune Homme", "H", 14, 18),
                    new RelaySlotDto("Jeune Dame", "D", 14, 18),
                    new RelaySlotDto("Vétéran Homme", "H", 35),
                    new RelaySlotDto("Vétérane Dame", "D", 35),
                    new RelaySlotDto("Dame 1", "D", 16),
                    new RelaySlotDto("Dame 2", "D", 16),
                    new RelaySlotDto("Homme 1", "H", 16),
                    new RelaySlotDto("Homme 2", "H", 16),
                ],
                legs: [50, 30, 20, 50, 30, 20, 30, 50],
            ),
            new RelayFormatDto(
                id: "cfc_n2",
                name: "Nationale 2 (8 coureurs)",
                group_id: "cfc",
                group: "CFC",
                team_size: 8,
                rules: [
                    "1 jeune Homme (H14 à H18)",
                    "1 jeune Dame (D14 à D18)",
                    "1 H35 et +",
                    "1 D35 et +",
                    "2 Dames (D16 et +)",
                    "2 Hommes (H16 et +)",
                ],
                description: "Parcours : 40′, 30′, 20′, 40′, 30′, 20′, 30′, 40′. Total : 250 min.",
                slots: [
                    new RelaySlotDto("Jeune Homme", "H", 14, 18),
                    new RelaySlotDto("Jeune Dame", "D", 14, 18),
                    new RelaySlotDto("Vétéran Homme", "H", 35),
                    new RelaySlotDto("Vétérane Dame", "D", 35),
                    new RelaySlotDto("Dame 1", "D", 16),
                    new RelaySlotDto("Dame 2", "D", 16),
                    new RelaySlotDto("Homme 1", "H", 16),
                    new RelaySlotDto("Homme 2", "H", 16),
                ],
                legs: [40, 30, 20, 40, 30, 20, 30, 40],
            ),
            new RelayFormatDto(
                id: "cfc_n3",
                name: "Nationale 3 (6 coureurs)",
                group_id: "cfc",
                group: "CFC",
                team_size: 6,
                rules: [
                    "1 jeune Homme ou Dame (D/H14 à D/H18)",
                    "2 Dames (D16 et +)",
                    "3 coureurs (D/H16 et +)",
                ],
                description: "Parcours : 30′, 40′, 20′, 30′, 40′, 30′. Total : 190 min.",
                slots: [
                    new RelaySlotDto("Jeune", null, 14, 18),
                    new RelaySlotDto("Dame 1", "D", 16),
                    new RelaySlotDto("Dame 2", "D", 16),
                    new RelaySlotDto("Coureur 1", null, 16),
                    new RelaySlotDto("Coureur 2", null, 16),
                    new RelaySlotDto("Coureur 3", null, 16),
                ],
                legs: [30, 40, 20, 30, 40, 30],
            ),
            new RelayFormatDto(
                id: "cfc_n4",
                name: "Nationale 4 (6 coureurs)",
                group_id: "cfc",
                group: "CFC",
                team_size: 6,
                rules: [
                    "1 jeune Homme ou Dame (D/H14 à D/H18)",
                    "2 Dames (D16 et +)",
                    "2 coureurs (D/H16 et +)",
                    "1 coureur (D/H14 et +)",
                ],
                description: "Parcours : 30′, 40′, 20′, 20′, 40′, 30′. Total : 180 min.",
                slots: [
                    new RelaySlotDto("Jeune", null, 14, 18),
                    new RelaySlotDto("Dame 1", "D", 16),
                    new RelaySlotDto("Dame 2", "D", 16),
                    new RelaySlotDto("Coureur 1", null, 16),
                    new RelaySlotDto("Coureur 2", null, 16),
                    new RelaySlotDto("Coureur 3", null, 14),
                ],
                legs: [30, 40, 20, 20, 40, 30],
            ),

            // ===== Relais-Sprint (Article 10) =====
            new RelayFormatDto(
                id: "relais_sprint",
                name: "Relais-Sprint (4 coureurs)",
                group_id: "relais_sprint",
                group: "Relais-Sprint",
                team_size: 4,
                rules: [
                    "2 Dames et 2 Hommes",
                    "D/H14 et + requis",
                    "Ordre : Dame, Homme, Homme, Dame",
                ],
                description: "Chaque relayeur : 12–15 min. Niveau orange (sprint urbain).",
                slots: [
                    new RelaySlotDto("Dame 1", "D", 14),
                    new RelaySlotDto("Homme 1", "H", 14),
                    new RelaySlotDto("Homme 2", "H", 14),
                    new RelaySlotDto("Dame 2", "D", 14),
                ],
                legs: [14, 14, 14, 14],
                ordered: true,
            ),

            // ===== CNE — Critérium National des Équipes (Article 11) =====
            new RelayFormatDto(
                id: "cne_hommes",
                name: "CNE Hommes (7 coureurs)",
                group_id: "cne",
                group: "CNE",
                team_size: 7,
                rules: [
                    "Tous H16 et +",
                    "Panachages d'âge et de sexe autorisés (Dames acceptées en cat. Hommes)",
                    "Relais 1–2 de nuit, relais 3 mixte, relais 4–7 de jour",
                ],
                description: "Total : 290 min. Nuit + jour. Niveau violet.",
                slots: self::simpleSlots(7, null, 16),
                legs: [40, 40, 50, 30, 50, 30, 50],
            ),
            new RelayFormatDto(
                id: "cne_dames",
                name: "CNE Dames (5 coureurs)",
                group_id: "cne",
                group: "CNE",
                team_size: 5,
                rules: [
                    "Toutes D16 et +",
                    "Relais 1 de nuit",
                ],
                description: "Total : 170 min. Nuit + jour. Niveau violet.",
                slots: self::simpleSlots(5, "D", 16),
                legs: [30, 40, 30, 40, 30],
            ),
            new RelayFormatDto(
                id: "cne_jeunes",
                name: "CNE Jeunes (4 coureurs)",
                group_id: "cne",
                group: "CNE",
                team_size: 4,
                rules: [
                    "D/H14 et D/H16 uniquement",
                    "Au moins 1 féminine dans l'équipe",
                ],
                description: "Total : 120 min. Niveau orange/jaune.",
                slots: [
                    new RelaySlotDto("Féminine", "D", 14, 16),
                    new RelaySlotDto("Coureur 2", null, 14, 16),
                    new RelaySlotDto("Coureur 3", null, 14, 16),
                    new RelaySlotDto("Coureur 4", null, 14, 16),
                ],
                legs: [30, 30, 30, 30],
            ),

            // ===== Trophées spéciaux =====
            new RelayFormatDto(
                id: "trophee_gueorgiou",
                name: "Trophée Thierry Gueorgiou (4 coureurs)",
                group_id: "trophees",
                group: "Trophées",
                team_size: 4,
                rules: [
                    "D/H10 et + (9 ans et + au 31 déc.)",
                    "Relayeurs 1 et 3 : D/H14 et + (13 ans et +)",
                    "Classement spécial si uniquement D/H16 et moins",
                ],
                description: "Parcours : 30′ (jaune), 20′ (bleu), 30′ (jaune), 20′ (bleu).",
                slots: [
                    new RelaySlotDto("Relayeur 1", null, 14),
                    new RelaySlotDto("Relayeur 2", null, 10),
                    new RelaySlotDto("Relayeur 3", null, 14),
                    new RelaySlotDto("Relayeur 4", null, 10),
                ],
                legs: [30, 20, 30, 20],
            ),

            // ===== Relais de couleur (open) =====
            new RelayFormatDto(
                id: "relais_couleur_vert",
                name: "Open Vert (2 coureurs)",
                group_id: "relais_couleur",
                group: "Relais de couleur",
                team_size: 2,
                rules: ["D/H10 et +", "Panachage clubs autorisé"],
                description: "20 min/relayeur. Niveau vert.",
            ),
            new RelayFormatDto(
                id: "relais_couleur_bleu",
                name: "Open Bleu (2 coureurs)",
                group_id: "relais_couleur",
                group: "Relais de couleur",
                team_size: 2,
                rules: ["D/H12 et +", "Panachage clubs autorisé"],
                description: "25–30 min/relayeur. Niveau bleu.",
            ),
            new RelayFormatDto(
                id: "relais_couleur_jaune",
                name: "Open Jaune (2 coureurs)",
                group_id: "relais_couleur",
                group: "Relais de couleur",
                team_size: 2,
                rules: ["D/H14 et +", "Panachage clubs autorisé"],
                description: "25–30 min/relayeur. Niveau jaune.",
            ),
            new RelayFormatDto(
                id: "relais_couleur_orange",
                name: "Open Orange (2 coureurs)",
                group_id: "relais_couleur",
                group: "Relais de couleur",
                team_size: 2,
                rules: ["D/H16 et +", "Panachage clubs autorisé"],
                description: "30 min/relayeur. Niveau orange.",
            ),
            new RelayFormatDto(
                id: "relais_couleur_violet",
                name: "Open Violet (2 coureurs)",
                group_id: "relais_couleur",
                group: "Relais de couleur",
                team_size: 2,
                rules: ["D/H16 et +", "Panachage clubs autorisé"],
                description: "30 min/relayeur. Niveau violet.",
            ),
        ];
    }
}
