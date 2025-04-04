<?php

class GroupService
{
    static function renderTags($groups, $delimiter = false)
    {
        $is_groups = $groups && (count($groups) > 0);
        echo $is_groups ? "<div class='grid-tag'>" : "";
        foreach ($groups as $group): ?>
            <div class="tag tag-<?= $group->color->value ?>"><?= $group->name ?></div>
        <?php endforeach;
        echo $is_groups ? "</div>" : "";
        echo ($is_groups && $delimiter) ? "<hr>" : "";
    }

    static function getAllEventGroups($event)
    {
        if (!$event || !$event->id) {
            // For new events, just return all groups without checking associations
            return em()->createQueryBuilder()
                ->select('g.id, g.name')
                ->addSelect("0 as has_group")
                ->from(UserGroup::class, 'g')
                ->orderBy('g.name', 'ASC')
                ->getQuery()
                ->getArrayResult();
        }
        // For existing events, check associations
        return em()->createQueryBuilder()
            ->select('g.id, g.name')
            ->addSelect('(CASE WHEN COUNT(eg.id) > 0 THEN 1 ELSE 0 END) as has_group')
            ->from(UserGroup::class, 'g')
            ->leftJoin('g.events', 'eg', 'WITH', 'eg = :event_id')
            ->setParameter('event_id', $event)
            ->groupBy('g.name')
            ->orderBy('g.name', 'ASC')
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
            ->getQuery()
            ->getArrayResult();
    }

    public static function getAvailableMembers(UserGroup $group): array
    {
        return em()->createQueryBuilder()
            ->select('PARTIAL u.{id, first_name, last_name}')
            ->from(User::class, 'u')
            ->leftJoin('u.groups', 'g')
            ->where('g.id != :group_id OR g.id IS NULL')
            ->orderBy('u.first_name, u.last_name')
            ->setParameter('group_id', $group->id)
            ->getQuery()
            ->getArrayResult();
    }

    static function renderGroupChoice($groups)
    { ?>
        <details class="dropdown">
            <summary aria-haspopup="listbox" data-intro="Lier à un groupe">Ajouter le groupe...
            </summary>
            <ul data-placement=top>
                <?php foreach ($groups as $group): ?>
                    <li>
                        <label>
                            <input type="checkbox" name="add_groups[]" value="<?= $group['id'] ?>" <?= $group['has_group'] ? "checked" : "" ?>>
                            <?= "{$group['name']}" ?>
                        </label>
                    </li>
                <?php endforeach ?>
            </ul>
        </details>
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

    static function renderUserGroupChoice($user)
    {
        $groups = self::getAllUserGroups($user);
        echo self::renderGroupChoice($groups);
    }

    static function listGroups()
    {
        return em()->createQueryBuilder()
            ->select('g')
            ->from(UserGroup::class, 'g')
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}