<?php
restrict_access(Access::$ROOT);
$user_id = get_route_param("user_id");
$user = User::get($user_id);

$v_activation = new Validator(action: "activation_form");
$activationLink = "";
if ($v_activation->valid()) {
    $activationLink = AuthService::create()->createActivationLink($user);
    Toast::success("Lien créé");
}

$user_features = FeatureService::list_user($user_id);
$club_features = FeatureService::list_club();
$v_features = new Validator(action: "features");
$feature_options = [];
foreach ($club_features as $f) {
    $feature_options[$f->featureName] = $f->featureName;
}
$features = $v_features->select("add_new")->options($feature_options)->label("New feature")->required();

if ($v_features->valid()) {
    $newFeature = $user_features[$features->value] ?? new UserFeature($user, Feature::from($features->value));
    $newFeature->enabled = true;
    em()->persist($newFeature);
    em()->flush();
    Toast::success("Fonctionnalité ajoutée");
    reload();
}

$v_removeFeature = new Validator(action: "remove_feature");
if ($v_removeFeature->valid() && isset($_POST['remove_name']) && isset($user_features[$_POST['remove_name']])) {
    em()->remove($user_features[$_POST['remove_name']]);
    em()->flush();
    Toast::error("Fonctionnalité retirée");
    reload();
}

page("$user->first_name $user->last_name - Debug") ?>
<?= actions()->link("/licencies/$user_id/modifier", "Settings", "fa-laptop-code") ?>
<section>
    <h2>Infos</h2>
    <table class="striped">
        <ul>
            <li>
                Permission -
                <?= $user->permission->value ?>
            </li>
            <li>
                Status -
                <?= $user->status->value ?>
            </li>
        </ul>
    </table>
</section>
<hr>
<section>
    <form method="post" hx-swap="innerHTML show:#activation:top">
        <div class=activation-header>
            <h3 id="activation">Activation
                <sl-tooltip content="A n'utiliser qu'en cas de problème avec les emails d'activation"><i
                        class="fas fa-circle-info"></i></sl-tooltip>
            </h3>
        </div>
        <?= $v_activation ?>
        <input type="submit" class="outline" name="createLink" value="Créer le lien">
        <?php if ($activationLink): ?>
            <label for="activationLink">Copier le lien ci-dessous pour l'envoyer à l'utilisateur</label>
            <div style="display:flex;align-items:center">
                <input id="activationLink" style="margin:0" type="text" value="<?= $activationLink ?>" readonly>
                <sl-copy-button from="activationLink.value"></sl-copy-button>
            </div>
        <?php endif ?>
    </form>
</section>
<hr>
<section>
    <h3>Fonctionnalités</h3>
    <ul>
        <?php if (!$club_features): ?>
            Pas de fonctionnalités disponibles pour ce club, contactez les développeurs pour accéder au nouvelles
            fonctionnalités 🚀
        <?php else: ?>
            <?php foreach ($user_features as $user_feature): ?>
                <li style="display:flex;align-items:center;gap:1rem;padding: 0.5rem">
                    <?= $user_feature->featureName ?>
                    <form method="post">
                        <?= $v_removeFeature ?>
                        <input type="hidden" name="remove_name" value="<?= $user_feature->featureName ?>">
                        <button class="destructive">Retirer</button>
                    </form>
                </li>
            <?php endforeach ?>
        </ul>
        <form method="post">
            <?= $v_features ?>
            <?= $features ?>
            <button>Ajouter</button>
        </form>
    <?php endif ?>
</section>

<style>
    .activation-header {
        display: flex;
        align-items: center;
    }
</style>