<?php
restrict_access();
require __DIR__ . "/../../../components/user_card.php";
$search = strtolower(get_query_param("search", numeric: false) ?? "");
if (!$search)
    return;
$users = em()->createQuery("SELECT u 
    FROM User u 
    WHERE (u.first_name LIKE :s OR u.last_name LIKE :s) 
    AND u.id <> :uid 
    ORDER BY u.last_name, u.first_name")
    ->setParameters(["s" => "$search%", "uid" => User::getMainUserId()])->getResult();

foreach ($users as $u): ?>
    <?php UserCard(
        user: $u,
        user_link: function () use ($u) { ?>
        <a href="/messages/direct/<?= $u->id ?>">
            <?= "{$u->first_name} {$u->last_name}" ?>
        </a>
        <?php
            }
    ) ?>
<?php endforeach ?>