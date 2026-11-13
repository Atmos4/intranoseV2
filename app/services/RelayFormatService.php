<?php

class RelayFormatService
{
    /** @return RelayGroup[] */
    public static function groups(): array
    {
        return em()->getRepository(RelayGroup::class)->findBy([], ['position' => 'ASC']);
    }

    public static function getGroup(string $id): ?RelayGroup
    {
        return em()->find(RelayGroup::class, $id);
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

    public static function get(string $id): ?RelayFormat
    {
        return em()->find(RelayFormat::class, $id);
    }

    /** @return RelayFormat[] */
    public static function byGroup(string $groupId): array
    {
        return em()->getRepository(RelayFormat::class)->findBy(['group' => $groupId], ['position' => 'ASC']);
    }

    /** @return RelayFormat[] */
    public static function all(): array
    {
        return em()->getRepository(RelayFormat::class)->findBy([], ['position' => 'ASC']);
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

    /** @return RelaySlot[] - unsaved slots, handy to build a format */
    public static function simpleSlots(int $count, ?string $sex = null, ?int $min = null, ?int $max = null): array
    {
        $slots = [];
        for ($i = 0; $i < $count; $i++) {
            $slots[] = new RelaySlot("Relayeur " . ($i + 1), $sex, $min, $max);
        }
        return $slots;
    }

    /**
     * Validate team composition against slots.
     * @param RelaySlot[] $slots
     * @param string[] $team_members - team member array : [ id => ['id' => int, 'name' => string, 'picture' => string, 'category' => string]]
     * @return array{filled: int, total: int, slots: array, extras: array} - slots: each slot => matched category or null; extras: leftover team members (full data, in original team order) that matched no slot
     */
    public static function validateComposition(array $slots, array $team_members): array
    {
        // Keep $unmatched keyed like $team_members (insertion order) so leftover "extras" stay in team order
        $unmatched = $team_members;

        // Sort slot indices by specificity (most constrained first)
        $indices = array_keys($slots);
        usort($indices, function ($a, $b) use ($slots) {
            return self::slotSpecificity($slots[$b]) - self::slotSpecificity($slots[$a]);
        });

        $assignments = [];
        foreach ($indices as $i) {
            $assignments[$i] = null;
            foreach ($unmatched as $id => $member) {
                if ($member && $slots[$i]->matches($member['category'] ?? null)) {
                    $assignments[$i] = $member['category'];
                    unset($unmatched[$id]);
                    break;
                }
            }
        }
        ksort($assignments);

        $filled = count(array_filter($assignments, fn($v) => $v !== null));
        $extras = array_values(array_filter($unmatched));
        return ['filled' => $filled, 'total' => count($slots), 'slots' => $assignments, 'extras' => $extras];
    }

    private static function slotSpecificity(RelaySlot $slot): int
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
     *
     * @return array{
     *   team_relay_format: string,
     *   current_format: ?RelayFormat,
     *   slot_defs: RelaySlot[],
     *   is_ordered: bool,
     *   team_members: array,
     *   member_categories: string[]
     * }
     */
    public static function resolveTeamContext(string|null $team_relay_format, $member_ids_raw, TeamGroup $team_group): array
    {
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
}
