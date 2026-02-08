<?php
restrict_access();
require __DIR__ . "/../../components/user_card.php";
$can_add_user = check_auth(Access::$EDIT_USERS);
$users = UserService::getActiveUserList();
page("Les licenciés")->enableHelp() ?>
<?= actions($can_add_user)->link("/licencies/ajouter", "Ajouter un licencié", "fa-plus")
    ->dropdown(
        fn($b) => $b
            ->link(
                "/familles",
                "Familles",
                "fa-users",
            )
            ->link(
                "/licencies/inactif",
                "Licenciés inactifs",
                "fa-user-lock"
            )
            ->link(
                "/licencies/desactive",
                "Licenciés désactivés",
                "fa-bed",
            )
            ->link(
                "/groupes",
                "Groupes",
                "fa-user-group"
            )
        ,
        "Plus",
        ["data-intro" => "Visualisez les familles et les licenciés désactivés ici"]
    ) ?>
<form method="get">
    <input type="search" id="search-users" name="search" placeholder="Rechercher..."
        onkeyup="searchSection('search-users','users-list')"
        data-intro="Utilisez la barre de recherche pour chercher votre licencié préféré !">
</form>

<section class="row" id="users-list">
    <?php foreach ($users as $user): ?>
        <?php $groups = GroupService::getUserGroups($user->id) ?>
        <div class="toggleWrapper col-sm-12 col-md-6">
            <?php UserCard(
                $user,
                subtitle: function ($user) use ($groups) {
                            GroupService::renderDots($groups);

                        }
            ) ?>
        </div>
    <?php endforeach ?>
</section>

<?= UserModal::renderRoot() ?>

<script src="/assets/js/section-search.js"></script>