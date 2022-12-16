<?php
restrict_access();

require_once "database/inscriptions_data.php";
$deplacements = get_deplacements();
$user_id = $_SESSION["user_id"];

page("Mes inscriptions", "inscriptions_list.css");
?>

<main class="container">
    <table role="grid">
        <thead class=header-responsive>
            <tr>
                <th></th>
                <th>Nom</th>
                <th>Date</th>
                <th>Limite d'inscription</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($deplacements as $deplacement) : ?>
                <tr class="inscription-row" onclick="window.location.href = '/mes-inscriptions/details/<?= $deplacement['did'] ?>'">
                    <td class="course-entry">
                        <?php if (is_registered($deplacement, $user_id)) : ?>
                            <ins><i class="fas fa-check"></i></ins>
                        <?php else : ?>
                            <del><i class="fas fa-xmark"></i></del>
                        <?php endif ?>
                    </td>
                    <td class="course-name"><b><?= $deplacement['nom'] ?></b></td>
                    <td class="course-date">
                        <div>
                            <?php include "components/start_icon.php" ?>
                            <span data-tooltip="Départ" data-placement="right"><?= $deplacement['depart'] ?></span>
                        </div>

                        <div>
                            <?php include "components/finish_icon.php" ?>
                            <span data-tooltip="Retour" data-placement="right"><?= $deplacement['arrivee'] ?></span>
                        </div>

                    </td>

                    <td class="course-limite">

                        <!-- <i class="fas fa-warning"></i> -->
                        <div><span class=responsive-description>Limite d'inscription : </span><?= $deplacement['limite'] ?></div>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</main>