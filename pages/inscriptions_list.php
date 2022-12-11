<?php
restrict_access();

require_once "database/inscriptions_data.php";
$deplacements = get_deplacements();
$user_id = $_SESSION["user_id"];

page("Mes inscriptions", "inscriptions.css");
?>

<main class="container">
    <figure>

        <table role="grid">
            <thead>
                <tr>
                    <th></th>
                    <th>Nom</th>
                    <th>Date</th>
                    <th>Limite d'inscription</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($deplacements as $deplacement) : ?>
                    <tr onclick="window.location.href = '/mes-inscriptions/details/<?= $deplacement['did'] ?>'">
                        <?php if (is_registered($deplacement, $user_id)) : ?>
                            <td><img class="register-status" src="/assets/icon/check-32x32.png" /></td>
                        <?php else : ?>
                            <td><img class="register-status" src="/assets/icon/cross-32x32.png" /></td>
                        <?php endif ?>
                        <td><b><?= $deplacement['nom'] ?></b></td>
                        <td>
                            <div class="inscription-date"><?= $deplacement['depart'] ?></div>
                            <div class="inscription-date"><?= $deplacement['arrivee'] ?></div>
                        </td>

                        <td><?= $deplacement['limite'] ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </figure>
</main>