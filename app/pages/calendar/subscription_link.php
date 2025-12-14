<?php
restrict_access();

$club = ClubManagementService::create()->getClub();
$baseUrl = env("BASE_URL");

// Generate webcal URL (replaces http:// or https:// with webcal://)
$icsUrl = "$baseUrl/calendrier/club.ics";
$webcalUrl = preg_replace('/^https?:\/\//', 'webcal://', $icsUrl);

page("Abonnement au calendrier")->css("about.css");
?>

<?= actions()->back('/evenements') ?>

<article>
    <p>Synchronisez automatiquement tous les événements de <strong><?= $club->name ?></strong> avec votre calendrier
        personnel : </p>

    <div style="display:flex;align-items:center">
        <input type="text" readonly value="<?= $webcalUrl ?>" id="webcal-url" onclick="this.select()"
            style="margin:0" />
        <sl-copy-button from="webcal-url.value"></sl-copy-button>
    </div>

    <hr>

    <h2>Informations importantes</h2>
    <ul>
        <li><strong>Mise à jour automatique :</strong> Les nouveaux événements et modifications apparaîtront
            automatiquement dans votre calendrier</li>
        <li><strong>Fréquence :</strong> Les calendriers se synchronisent généralement toutes les heures (selon
            votre application)</li>
        <li><strong>Lecture seule :</strong> Vous ne pouvez pas modifier les événements depuis votre calendrier
            personnel</li>
        <li><strong>Contenu :</strong> Seuls les événements publiés sont inclus dans le calendrier</li>
        <li><strong>Rappels :</strong> Les deadlines d'inscription sont incluses comme rappels</li>
    </ul>
</article>