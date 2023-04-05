<?php
restrict_access();
$users = em()->getRepository(User::class)->findBy([], ['last_name' => 'ASC', 'first_name' => 'ASC']);
page("Les licenciés")->css("user_list.css");
?>
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
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<script src="/assets/js/table-search.js"></script>