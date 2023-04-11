<?php
restrict_access();
$can_add_user = check_auth(Access::$EDIT_USERS);
$users = em()->getRepository(User::class)->findBy([], ['last_name' => 'ASC', 'first_name' => 'ASC']);
page("Les licenciés")->css("user_list.css");
?>

<?php if ($can_add_user): ?>
    <p class="center">
        <a role="button" href="/licencies/add" class="contrast outline"><i class="fas fa-plus"></i> Ajouter un licencié</a>
    </p>
<?php endif ?>

<form method="get">
    <input type="search" id="search-users" name="search" placeholder="Rechercher..."
        onkeyup="searchTable('search-users','users-table')">
</form>
<table id="users-table">
    <thead>
        <tr>
            <th scope="col">Nom</th>
            <th scope="col">Prénom</th>
            <th scope="col">Email</th>
            <th scope="col">Portable</th>
            <?php if ($can_add_user): ?>
                <th scope="col"></th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr class="clickable" onclick="window.location.href = '/licencies/<?= $user->id ?>'">
                <td class="lastname">
                    <?= $user->last_name ?>
                </td>
                <td class="firstname">
                    <?= $user->first_name ?>
                </td>
                <td class="email">
                    <?= $user->nose_email ?>
                </td>
                <td class="phone-number">
                    <?= $user->phone ?>
                </td>
                <?php if ($can_add_user): ?>
                    <td class="">
                        <a href="/licencies/<?= $user->id ?>/supprimer" class="destructive">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<script src="/assets/js/table-search.js"></script>