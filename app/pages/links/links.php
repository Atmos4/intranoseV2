<?php restrict_access();
$user = User::getCurrent();
$jot_params = "?nom=" . $user->last_name . "&prenom=" .
    $user->first_name . "&email=" . $user->real_email;
$jot_link = new Link(
    "https://form.jotform.com/250042921370345$jot_params",
    "JotForm",
    "Lien pour accéder au formulaire de remboursement."
);

$links = em()->createQuery("SELECT l FROM Link l")->getResult();

page("Liens utiles")->css("links.css");
?>
<?php if (Feature::JootForm->on()): ?>
    <?= component(__DIR__ . "/link_line.php")->render(["link" => $jot_link, "disable_delete" => true]); ?>
<?php endif ?>
<?php if (empty($links) && !Feature::JootForm->on()): ?>
    Le club n'as pas encore de liens 😱
<?php endif ?>
<?php foreach ($links as $link): ?>
    <?= component(__DIR__ . "/link_line.php")->render(["link" => $link]); ?>
<?php endforeach ?>
<div id="new_link">
</div>
<?php if (check_auth(Access::$EDIT_USERS)): ?>
    <hr>
    <button class="outline" hx-post="/liens-utiles/nouveau" hx-target="#new_link"><i class="fas fa-plus"></i> Ajouter un
        lien</button>
<?php endif ?>