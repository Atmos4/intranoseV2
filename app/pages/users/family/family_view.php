<?php
restrict_access();
require __DIR__ . "/../../../components/user_card.php";
$user = User::getMain();
$family = em()->find(Family::class, get_route_param("family_id"));
if (!$family) {
    force_404("this family does not exist");
}
if (!$user->family_leader || $family != $user->family) {
    // Make sure we only authorize family leaders or admins on this page
    restrict_access(Access::$EDIT_USERS);
}
if (isset($_POST['add_members']) && count($_POST['add_members'])) {
    $count = count($_POST['add_members']);
    $dql = "UPDATE User u SET u.family = ?$count where u.id IN ("
        . implode(",", array_map(function ($value) {
            if (!is_numeric($value)) {
                die("Méchant Dobby");
            }
            return "?$value";
        }, array_keys($_POST['add_members']))) . ")";
    em()->createQuery($dql)->setParameters([...$_POST['add_members'], $count => $family->id])->execute();
    redirect("/famille/$family->id");
}
$add_member_list = em()->createQueryBuilder()
    ->select("u.id, u.last_name, u.first_name")
    ->from(User::class, 'u')
    ->where('u.family IS NULL')
    ->orderBy('u.first_name')
    ->orderBy('u.last_name')
    ->getQuery()->getArrayResult();

page($family->name)->css("family_list.css")->enableHelp() ?>

<?= actions(check_auth(Access::$EDIT_USERS))
    ->back("/familles")
    ->dropdown(fn($b) => $b->link("/famille/$family->id/supprimer", "Supprimer la famille", "fa fa-trash", ["class" => "destructive outline"])) ?>


<section class="row">
    <?php foreach ($family->members as $key => $f_member): ?>
        <div class="col-sm-12 col-md-6">
            <?php
            UserCard(
                $f_member,
                subtitle: function ($user) use ($key) { ?>
                <div <?= "data-intro=" . $key == 0 ? "\"Un membre de la famille peut être Parent ou Enfant\"" : "" ?>>
                    <?= $user->family_leader ? "Parent" : "Enfant" ?>
                </div>
            <?php },
                actions: function ($user) use ($family, $key) {
                    if ($user->id != User::getMain()->id || check_auth(Access::$EDIT_USERS)): ?>
                    <details class="dropdown" dir="rtl">
                        <summary aria-haspopup="listbox" class="contrast actions" <?= $key == 0 ? "data-intro=\"Vous pouvez modifier ce rôle\"" : "" ?>>
                            <i class="fa fa-ellipsis-vertical"></i>
                        </summary>
                        <ul dir="rtl">
                            <li>
                                <a href="/famille/<?= $family->id ?>/change/<?= $user->id ?>" class="contrast">
                                    Changer rôle
                                    <i class="fa fa-arrow-<?= $user->family_leader ? "down" : "up" ?>"></i>
                                </a>
                            </li>
                            <li>
                                <a href="/famille/<?= $family->id ?>/supprimer/<?= $user->id ?>" class="destructive">
                                    Retirer
                                    <i class="fa fa-xmark"></i>
                                </a>
                            </li>
                        </ul>
                    </details>
                <?php endif;
                }
            );
            ?>
        </div>
    <?php endforeach ?>
</section>

<?php if (check_auth(Access::$EDIT_USERS)): ?>
    <form method="post">
        <h4>Ajouter un membre</h4>
        <details class="dropdown">
            <summary aria-haspopup="listbox" data-intro="Ajoutez de nouveaux membres à la famille !">Ajouter à la
                famille...</summary>
            <ul data-placement=top>
                <?php foreach ($add_member_list as $add_member): ?>
                    <li>
                        <label>
                            <input type="checkbox" name="add_members[]" value="<?= $add_member['id'] ?>">
                            <?= "{$add_member['last_name']} {$add_member['first_name']}" ?>
                        </label>
                    </li>
                <?php endforeach ?>
            </ul>
        </details>
        <button type="submit">Ajouter</button>
    </form>
<?php endif ?>
<?= UserModal::renderRoot() ?>