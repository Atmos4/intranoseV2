<?php

class GroupService
{
    static function renderTags($groups, $delimiter = false, $is_div = true)
    {
        $is_groups = $groups && (count($groups) > 0);
        echo $is_groups && $is_div ? "<div id='groups'>" : "";
        foreach ($groups as $group): ?>
            <div class="tag tag-<?= $group->color->value ?>"><?= $group->name ?></div>
        <?php endforeach;
        echo $is_groups && $is_div ? "</div>" : "";
        echo ($is_groups && $delimiter) ? "<hr>" : "";
    }

    static function renderDots($groups)
    {
        $is_groups = $groups && (count($groups) > 0);
        echo $is_groups ? "<div class='dot-block'>" : "";
        $colorList = ThemeColor::colorsList();
        foreach ($groups as $group): ?>
            <sl-tooltip content="<?= $group->name ?>">
                <div class="color-dot" style="background-color:<?= $colorList[$group->color->value] ?>"></div>
            </sl-tooltip>
        <?php endforeach;
        echo $is_groups ? "</div>" : "";
    }

    static function getAllEventGroups($event)
    {
        if (!$event || !$event->id) {
            // For new events, just return all groups without checking associations
            return em()->createQueryBuilder()
                ->select('g.id, g.name')
                ->addSelect("0 as has_group")
                ->from(UserGroup::class, 'g')
                ->orderBy('LOWER(g.name)', 'ASC')
                ->getQuery()
                ->getArrayResult();
        }
        // For existing events, check associations
        return em()->createQueryBuilder()
            ->select('g.id, g.name')
            ->addSelect('(CASE WHEN COUNT(eg.id) > 0 THEN 1 ELSE 0 END) as has_group')
            ->from(UserGroup::class, 'g')
            ->leftJoin('g.events', 'eg', 'WITH', 'eg = :event_id')
            ->setParameter('event_id', $event->id)
            ->groupBy('g.name')
            ->orderBy('LOWER(g.name)', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    static function getEventGroups(string $eventId): array
    {
        return em()->createQueryBuilder()
            ->select('g')
            ->from(UserGroup::class, 'g')
            ->leftJoin('g.events', 'e')
            ->where('e.id = :eid')
            ->setParameter('eid', $eventId)
            ->getQuery()
            ->getResult();
    }

    static function getUserGroups(string $userId): array
    {
        return em()->createQueryBuilder()
            ->select('g')
            ->from(UserGroup::class, 'g')
            ->leftJoin('g.members', 'u')
            ->where('u.id = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()
            ->getResult();
    }

    static function getAllUserGroups($user)
    {
        return em()->createQueryBuilder()
            ->select('g.id, g.name')
            ->addSelect('(CASE WHEN COUNT(m.id) > 0 THEN true ELSE false END) as has_group')
            ->from(UserGroup::class, 'g')
            ->leftJoin('g.members', 'm', 'WITH', 'm = :user')
            ->setParameter('user', $user)
            ->groupBy('g.id, g.name')
            ->orderBy('LOWER(g.name)', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public static function getAvailableMembers(UserGroup $group): array
    {
        return em()->createQueryBuilder()
            ->select('u.id, u.first_name, u.last_name')
            ->from(User::class, 'u')
            ->where('NOT EXISTS (SELECT 1 FROM UserGroup g JOIN g.members m WHERE g.id = :group_id AND m.id = u.id)')
            ->orderBy('u.first_name, u.last_name')
            ->setParameter('group_id', $group->id)
            ->getQuery()
            ->getArrayResult();
    }

    static function renderGroupChoice($groups)
    {
        $checked_groups = array_filter($groups, fn($group) => $group['has_group'] ?? false);
        ?>
        <fieldset>
            <legend style="margin-bottom: 0;">Groupes</legend>
            <sl-select multiple clearable name="add_groups[]"
                value="<?= implode(' ', array_map(fn($g) => $g["id"], $checked_groups)) ?>">
                <?php foreach ($groups as $group): ?>
                    <sl-option value="<?= $group["id"] ?>">
                        <?= "{$group['name']}" ?>
                    </sl-option>
                <?php endforeach ?>
            </sl-select>
        </fieldset>
        <?php
    }

    static function renderEventGroupChoice($event)
    {
        $groups = self::getAllEventGroups($event);
        echo self::renderGroupChoice($groups);
    }

    static function getGroups($list = [])
    {
        return em()->createQueryBuilder()
            ->select('g')
            ->from(UserGroup::class, 'g')
            ->where('g.id IN (:ids)')
            ->setParameter('ids', $list)
            ->orderBy('LOWER(g.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    static function getAllGroups()
    {
        return em()->createQueryBuilder()
            ->select('g.id, g.name')
            ->from(UserGroup::class, 'g')
            ->orderBy('LOWER(g.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    static function processEventGroupChoice($event)
    {
        $event->groups->clear();
        $groups = self::getGroups($_POST['add_groups'] ?? []);

        foreach ($groups as $group) {
            if (!$event->groups->contains($group)) {
                $event->groups->add($group);
            }
        }
        em()->persist($event);
        //! need to flush after calling this function
    }

    /**
     *  need to flush after calling this function
     */
    static function processUserGroupChoice($user)
    {
        $user->groups->clear();
        $groups = self::getGroups($_POST['add_groups'] ?? []);

        foreach ($groups as $group) {
            if (!$user->groups->contains($group)) {
                $user->groups->add($group);
            }
        }
        em()->persist($user);
    }

    static function renderUserGroupChoice($user = null)
    {
        $groups = $user ? self::getAllUserGroups($user) : self::getAllGroups();
        echo self::renderGroupChoice($groups);
    }

    static function listGroups()
    {
        return em()->createQueryBuilder()
            ->select('g')
            ->from(UserGroup::class, 'g')
            ->orderBy('LOWER(g.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    static function RenderGroupsWarning(User $user, Event $event)
    {
        $users_from_event_groups = em()->createQueryBuilder()
            ->select('egm.id')
            ->from(Event::class, 'e')
            ->innerJoin('e.groups', 'eg')
            ->leftJoin('eg.members', 'egm')
            ->where('e.id = :eid')
            ->setParameters(['eid' => $event->id])
            ->getQuery()
            ->getResult();
        # first check if there are groups in the event, then if the user is in the groups
        if (!$users_from_event_groups) {
            return;
        }
        foreach ($users_from_event_groups as $result) {
            if ($result['id'] === $user->id) {
                return;
            }
        }
        ?>
        <article class="notice invalid" ?>
            Attention, l'événement concerne des groupes dont vous ne faites pas partie :
            <?= GroupService::renderTags($event->groups, is_div: false) ?>
        </article>
        <?php
    }
}
