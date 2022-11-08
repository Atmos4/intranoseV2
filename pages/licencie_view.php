<?php
require_once "database/licencie_data.php";
$licencie = get_licencie(get_route_param('licencie_id'));

page($licencie['prenom'] . " " . $licencie['nom'], "licencie.css");

$profile_picture = (file_exists("images/profile/" . $licencie['id'] . ".jpg")) ? "/images/profile/" . $licencie['id'] . ".jpg" : "/images/profile/none.jpg";
?>
<main class="container">
    <a href="/les-licencies" class="contrast return-link">Retour aux licenciés</a>
    <article class="user-infos grid">
        <figure>
            <img src="<?= $profile_picture ?>">
        </figure>
        <table class="infos-table">
            <tr>
                <td>Catégorie</td>
                <td> <?= $licencie['category_name'] ?></td>
            </tr>
            <tr>
                <td>Email</td>
                <td> <?= $licencie['email'] ?></td>
            </tr>
            <tr>
                <td>Adresse</td>
                <td> <?= join(
                            "<br/>",
                            [
                                $licencie['adresse1'] . " " . $licencie['adresse2'],
                                $licencie['cp'] . " " . $licencie['ville']
                            ]
                        ) ?></td>
            </tr>
            <tr>
                <td>📞 Fixe</td>
                <td> <?= $licencie['tel'] ?></td>
            </tr>
            <tr>
                <td>📞 Portable </td>
                <td><?= $licencie['telport'] ?></td>
            </tr>
            <tr>
                <td>Licence</td>
                <td><?= $licencie['num_lic'] ?></td>
            </tr>
            <tr>
                <td>Sport Ident</td>
                <td><?= $licencie['sportident'] ?></td>
            </tr>
        </table>
    </article>

</main>