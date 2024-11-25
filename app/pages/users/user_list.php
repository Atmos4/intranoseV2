<?php
restrict_access();
$can_add_user = check_auth(Access::$EDIT_USERS);
$users = UserService::getActiveUserList();
page("Les licenciés") ?>
<?= actions($can_add_user)->link("/licencies/ajouter", "Ajouter un licencié", "fa-plus")
    ->dropdown(
        fn($b) => $b
            ->link("/familles", "Familles", "fa-users")
            ->link("/licencies/desactive", "Licenciés désactivés", "fa-bed"),
        "Plus"
    ) ?>
<form method="get">
    <input type="search" id="search-users" name="search" placeholder="Rechercher..."
        onkeyup="searchSection('search-users','users-list')">
</form>

<section class="row" id="users-list">
    <?php foreach ($users as $user): ?>
        <div class="toggleWrapper col-sm-12 col-md-6">
            <article class="user-card">
                <img src="<?= $user->getPicture() ?>">
                <a href="/licencies?user=<?= $user->id ?>" <?= UserModal::props($user->id) ?>>
                    <?= "$user->first_name $user->last_name" ?>
                </a>
            </article>
        </div>
    <?php endforeach ?>
</section>

<?= UserModal::renderRoot() ?>

<script src="/assets/js/section-search.js"></script>