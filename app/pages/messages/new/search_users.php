<?php
restrict_access();
$search = strtolower(get_query_param("search", numeric: false) ?? "");
if (!$search)
    return;
$users = em()->createQuery("SELECT u.id, u.last_name, u.first_name, u.picture 
    FROM User u 
    WHERE (u.first_name LIKE :s OR u.last_name LIKE :s) 
    AND u.id <> :uid 
    ORDER BY u.last_name, u.first_name")
    ->setParameters(["s" => "$search%", "uid" => User::getMainUserId()])->getArrayResult();

foreach ($users as $u): ?>
    <article class="user-card">
        <img src="<?= User::getUserPicture($u['picture']) ?>" alt="">
        <div>
            <a href="/messages/direct/<?= $u['id'] ?>">
                <?= "{$u['first_name']} {$u['last_name']}" ?>
            </a>
        </div>
    </article>
<?php endforeach ?>