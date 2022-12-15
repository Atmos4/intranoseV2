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
                    <tr class="inscription-row" onclick="window.location.href = '/mes-inscriptions/details/<?= $deplacement['did'] ?>'">
                        <?php if (is_registered($deplacement, $user_id)) : ?>
                            <td><ins><i class="fas fa-check"></i></ins></td>
                        <?php else : ?>
                            <td><del><i class="fas fa-xmark"></i></del></td>
                        <?php endif ?>
                        <td><b><?= $deplacement['nom'] ?></b></td>
                        <td>
                            <div class="inscription-date">
                                <?php include "components/start_icon.php" ?>
                                <div>
                                    <span data-tooltip="Départ" data-placement="right"><?= $deplacement['depart'] ?></span>
                                </div>

                                <?php include "components/finish_icon.php" ?>
                                <div>
                                    <span data-tooltip="Retour" data-placement="right"><?= $deplacement['arrivee'] ?></span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="inscription-date">

                                <!-- <i class="fas fa-warning"></i> -->
                                <div><?= $deplacement['limite'] ?></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </figure>
</main>