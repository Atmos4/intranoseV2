<?php
restrict_access();
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

page($family->name)->css("family_list.css") ?>
<?php if (check_auth(Access::$EDIT_USERS)): ?>
    <nav id="page-actions">
        <a href="/familles" class="secondary"><i class="fa fa-caret-left"></i> Retour</a>
        <li>
            <details role="list" dir="rtl">
                <summary role="link" aria-haspopup="listbox" class="contrast">Actions</summary>
                <ul role="listbox">
                    <li>
                        <a href="<?= $family->id ?>/supprimer" class="destructive outline">
                            <i class="fa fa-trash"></i> Supprimer la famille
                        </a>
                    </li>
                </ul>
            </details>
        </li>
    </nav>
<?php endif ?>
<div class="row">
    <?php foreach ($family->members as $f_member): ?>
        <div class="col-sm-12 col-md-6">
            <article class="card">
                <?php
                $result_image = glob("assets/images/profile/" . $f_member->id . ".*");

                $profile_picture = (count($result_image) > 0 ?
                    "/" . $result_image[0]
                    : "/assets/images/profile/none.jpg");
                ?>
                <img src="<?= $profile_picture ?>">
                <div>
                    <a href="/licencies/<?= $f_member->id ?>"><?= "$f_member->first_name $f_member->last_name" ?></a>
                    <br>
                    <?= $f_member->family_leader ? "Parent" : "Enfant" ?>
                </div>
                <nav>
                    <ul>
                        <li>
                            <?php if ($f_member != $user || check_auth(Access::$EDIT_USERS)): ?>
                                <details role="list" dir="rtl">
                                    <summary aria-haspopup="listbox" class="contrast actions">
                                        <i class="fa fa-ellipsis-vertical"></i>
                                    </summary>
                                    <ul role="listbox">
                                        <li><a href="<?= "$family->id/change/$f_member->id" ?>" class="contrast">
                                                Changer rôle
                                                <i class="fa fa-arrow-<?= $f_member->family_leader ? "down" : "up" ?>"></i>
                                            </a></li>
                                        <li><a href="<?= "$family->id/supprimer/$f_member->id" ?>" class="destructive">
                                                Retirer
                                                <i class="fa fa-xmark"></i>
                                            </a></li>
                                    </ul>
                                </details>
                            <?php endif ?>
                        </li>
                    </ul>
                </nav>
            </article>
        </div>
    <?php endforeach ?>
</div>

<?php if (check_auth(Access::$EDIT_USERS)): ?>
    <form method="post">
        <h4>Ajouter un membre</h4>
        <details role="list">
            <summary aria-haspopup="listbox">Ajouter à la famille...</summary>
            <ul role="listbox">
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