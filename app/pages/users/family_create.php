<?php
restrict_access(Access::$EDIT_USERS);
$user = User::get(get_route_param("user_id"));
// If user has a family already we redirect to the existing family
if ($user->family) {
    redirect("/famille/{$user->family->id}");
}
// Else we display the creation warning
$v = new Validator(action: "create_family");
if ($v->valid()) {
    $f = new Family();
    $f->name = "Famille $user->last_name";
    $user->family = $f;
    $user->family_leader = true;
    em()->persist($f);
    em()->persist($user);
    em()->flush();
    redirect("/famille/{$f->id}");
}
page("Créer une famille") ?>
<form method="post">
    <?= $v->render_validation() ?>
    <div class="row center">
        <p>Vous êtes sur le point de créer une famille pour cet utilisateur. Cela lui permettra de prendre le contrôle
            de ses proches pour les inscrire aux courses, modifier leur profil, etc</p>
        <p>Êtes-vous sûr ?</p>
        <div class="col-auto">
            <a class="secondary" role="button" href="/licencies/user=<?= $user->id ?>">Annuler</a>
        </div>
        <div class="col-auto">
            <button type="submit">Créer</button>
        </div>
    </div>
</form>