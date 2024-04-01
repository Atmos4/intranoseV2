<?php
restrict_access();
$can_add_user = check_auth(Access::$EDIT_USERS);
$users = UserService::getActiveUserList();
page("Les licenciés")->css("user_list.css");
?>

<?php if ($can_add_user): ?>
    <nav id="page-actions">
        <a href="/licencies/ajouter"><i class="fas fa-plus"></i> Ajouter un licencié</a>
        <li>
            <details class="dropdown">
                <summary class="contrast">Plus</summary>
                <ul dir="rtl">
                    <li><a href="/familles" class="contrast"><i class="fas fa-users"></i> Familles</a></li>
                    <li>
                        <a href="/licencies/desactive" class="contrast"><i class="fas fa-bed"></i> Licenciés désactivés</a>
                    </li>
                </ul>
            </details>
        </li>
    </nav>
<?php endif ?>

<form method="get">
    <input type="search" id="search-users" name="search" placeholder="Rechercher..."
        onkeyup="searchSection('search-users','users-list')">
</form>

<section class="row" id="users-list">
    <?php foreach ($users as $user): ?>
        <div class="toggleWrapper col-sm-12 col-md-6">
            <article class="card">
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