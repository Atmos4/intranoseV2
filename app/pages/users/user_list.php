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
            <details role="list" dir="rtl">
                <summary role="link" aria-haspopup="listbox" class="contrast">Plus</summary>
                <ul role="listbox">
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
        <div class="col-sm-12 col-md-6">
            <article class="card">
                <?php
                $result_image = glob("assets/images/profile/" . $user->id . ".*");

                $profile_picture = (count($result_image) > 0 ?
                    "/" . $result_image[0]
                    : "/assets/images/profile/none.jpg");
                ?>
                <img src="<?= $profile_picture ?>">
                <div class="card-content">
                    <div id="name-div">
                        <a href="/licencies/<?= $user->id ?>">
                            <?= "$user->first_name $user->last_name" ?>
                        </a>
                    </div>
                    <div class="card-details">
                        <?= $user->nose_email ?>
                        <br>
                        <?= $user->phone ?>
                    </div>
                </div>
            </article>
        </div>
    <?php endforeach ?>
</section>

<script src="/assets/js/section-search.js"></script>