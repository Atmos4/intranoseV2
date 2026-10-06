<?php

declare(strict_types=1);

namespace intranose\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relay formats as database items (with default FFCO formats)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE orm_relay_groups (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, position INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE orm_relay_formats (id VARCHAR(255) NOT NULL, group_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, team_size INTEGER NOT NULL, rules CLOB NOT NULL --(DC2Type:json)
, description VARCHAR(255) DEFAULT NULL, ordered BOOLEAN NOT NULL, position INTEGER NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_A5850358FE54D947 FOREIGN KEY (group_id) REFERENCES orm_relay_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A5850358FE54D947 ON orm_relay_formats (group_id)');
        $this->addSql('CREATE TABLE orm_relay_slots (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, format_id VARCHAR(255) DEFAULT NULL, position INTEGER NOT NULL, label VARCHAR(255) NOT NULL, sex VARCHAR(1) DEFAULT NULL, min_category INTEGER DEFAULT NULL, max_category INTEGER DEFAULT NULL, leg_minutes INTEGER DEFAULT NULL, CONSTRAINT FK_70BC96A8D629F605 FOREIGN KEY (format_id) REFERENCES orm_relay_formats (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_70BC96A8D629F605 ON orm_relay_slots (format_id)');

        foreach (self::GROUPS as [$id, $name, $position]) {
            $this->addSql('INSERT INTO orm_relay_groups (id, name, position) VALUES (?, ?, ?)', [$id, $name, $position]);
        }
        foreach (self::FORMATS as [$id, $name, $group, $teamSize, $ordered, $position, $rules, $description, $slots]) {
            $this->addSql(
                'INSERT INTO orm_relay_formats (id, group_id, name, team_size, rules, description, ordered, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [$id, $group, $name, $teamSize, json_encode($rules, JSON_UNESCAPED_UNICODE), $description, (int) $ordered, $position]
            );
            foreach ($slots as $i => [$label, $sex, $min, $max, $leg]) {
                $this->addSql(
                    'INSERT INTO orm_relay_slots (format_id, position, label, sex, min_category, max_category, leg_minutes) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [$id, $i, $label, $sex, $min, $max, $leg]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE orm_relay_slots');
        $this->addSql('DROP TABLE orm_relay_formats');
        $this->addSql('DROP TABLE orm_relay_groups');
    }

    /** [id, name, position] */
    private const GROUPS = [
        ['relais_cat', 'Relais par catégorie', 0],
        ['cfc', 'Championnat de France des Clubs', 1],
        ['relais_sprint', 'Relais-Sprint', 2],
        ['cne', 'Critérium National des Équipes', 3],
        ['trophees', 'Trophées', 4],
        ['relais_couleur', 'Relais de couleur', 5],
    ];

    /** [id, name, group id, team size, ordered, position, rules, description, slots[label, sex, min category, max category, leg minutes]] */
    private const FORMATS = [
        ['relais_cat_d12', 'D12 / H12', 'relais_cat', 2, false, 0,
            ['2 coureurs D/H12'],
            'Temps total : 40 min. Niveau bleu.',
            [
                ['Relayeur 1', null, null, 12, null],
                ['Relayeur 2', null, null, 12, null],
            ]],
        ['relais_cat_d16', 'D16 / H16 (Dames : 2, Hommes : 3)', 'relais_cat', 3, false, 1,
            ['Dames : 2 coureurs D/H14–D/H16', 'Hommes : 3 coureurs D/H14–D/H16'],
            'Temps total : 60 min (D) / 90 min (H). Niveau jaune.',
            [
                ['Relayeur 1', null, 14, 16, null],
                ['Relayeur 2', null, 14, 16, null],
                ['Relayeur 3', null, 14, 16, null],
            ]],
        ['relais_cat_d20', 'D20 / H20 (Dames : 2, Hommes : 3)', 'relais_cat', 3, false, 2,
            ['Dames : 2 coureurs D/H16–D/H20', 'Hommes : 3 coureurs D/H16–D/H20'],
            'Temps total : 70 min (D) / 105 min (H). Niveau violet.',
            [
                ['Relayeur 1', null, 16, 20, null],
                ['Relayeur 2', null, 16, 20, null],
                ['Relayeur 3', null, 16, 20, null],
            ]],
        ['relais_cat_d21', 'D21 / H21', 'relais_cat', 3, false, 3,
            ['3 coureurs D/H18 et +'],
            'Temps total : 120 min. Niveau violet.',
            [
                ['Relayeur 1', null, 18, null, null],
                ['Relayeur 2', null, 18, null, null],
                ['Relayeur 3', null, 18, null, null],
            ]],
        ['relais_cat_d35', 'D35 / H35', 'relais_cat', 3, false, 4,
            ['3 coureurs D/H35 et +'],
            'Temps total : 105 min. Niveau violet.',
            [
                ['Relayeur 1', null, 35, null, null],
                ['Relayeur 2', null, 35, null, null],
                ['Relayeur 3', null, 35, null, null],
            ]],
        ['relais_cat_d45', 'D45 / H45', 'relais_cat', 3, false, 5,
            ['3 coureurs D/H45 et +'],
            'Temps total : 90 min. Niveau violet.',
            [
                ['Relayeur 1', null, 45, null, null],
                ['Relayeur 2', null, 45, null, null],
                ['Relayeur 3', null, 45, null, null],
            ]],
        ['relais_cat_d55', 'D55 / H55', 'relais_cat', 2, false, 6,
            ['2 coureurs D/H55 et +'],
            'Temps total : 60 min. Niveau violet.',
            [
                ['Relayeur 1', null, 55, null, null],
                ['Relayeur 2', null, 55, null, null],
            ]],
        ['relais_cat_d65', 'D65 / H65', 'relais_cat', 2, false, 7,
            ['2 coureurs D/H65 et +'],
            'Temps total : 60 min. Niveau violet.',
            [
                ['Relayeur 1', null, 65, null, null],
                ['Relayeur 2', null, 65, null, null],
            ]],
        ['relais_cat_d75', 'D75 / H75', 'relais_cat', 2, false, 8,
            ['2 coureurs D/H75 et +'],
            'Temps total : 60 min. Niveau violet.',
            [
                ['Relayeur 1', null, 75, null, null],
                ['Relayeur 2', null, 75, null, null],
            ]],
        ['cfc_n1', 'Nationale 1 (8 coureurs)', 'cfc', 8, false, 9,
            ['1 jeune Homme (H14 à H18)', '1 jeune Dame (D14 à D18)', '1 H35 et +', '1 D35 et +', '2 Dames (D16 et +)', '2 Hommes (H16 et +)'],
            'Parcours : 50′, 30′, 20′, 50′, 30′, 20′, 30′, 50′. Total : 280 min.',
            [
                ['Jeune Homme', 'H', 14, 18, 50],
                ['Jeune Dame', 'D', 14, 18, 30],
                ['Vétéran Homme', 'H', 35, null, 20],
                ['Vétérane Dame', 'D', 35, null, 50],
                ['Dame 1', 'D', 16, null, 30],
                ['Dame 2', 'D', 16, null, 20],
                ['Homme 1', 'H', 16, null, 30],
                ['Homme 2', 'H', 16, null, 50],
            ]],
        ['cfc_n2', 'Nationale 2 (8 coureurs)', 'cfc', 8, false, 10,
            ['1 jeune Homme (H14 à H18)', '1 jeune Dame (D14 à D18)', '1 H35 et +', '1 D35 et +', '2 Dames (D16 et +)', '2 Hommes (H16 et +)'],
            'Parcours : 40′, 30′, 20′, 40′, 30′, 20′, 30′, 40′. Total : 250 min.',
            [
                ['Jeune Homme', 'H', 14, 18, 40],
                ['Jeune Dame', 'D', 14, 18, 30],
                ['Vétéran Homme', 'H', 35, null, 20],
                ['Vétérane Dame', 'D', 35, null, 40],
                ['Dame 1', 'D', 16, null, 30],
                ['Dame 2', 'D', 16, null, 20],
                ['Homme 1', 'H', 16, null, 30],
                ['Homme 2', 'H', 16, null, 40],
            ]],
        ['cfc_n3', 'Nationale 3 (6 coureurs)', 'cfc', 6, false, 11,
            ['1 jeune Homme ou Dame (D/H14 à D/H18)', '2 Dames (D16 et +)', '3 coureurs (D/H16 et +)'],
            'Parcours : 30′, 40′, 20′, 30′, 40′, 30′. Total : 190 min.',
            [
                ['Jeune', null, 14, 18, 30],
                ['Dame 1', 'D', 16, null, 40],
                ['Dame 2', 'D', 16, null, 20],
                ['Coureur 1', null, 16, null, 30],
                ['Coureur 2', null, 16, null, 40],
                ['Coureur 3', null, 16, null, 30],
            ]],
        ['cfc_n4', 'Nationale 4 (6 coureurs)', 'cfc', 6, false, 12,
            ['1 jeune Homme ou Dame (D/H14 à D/H18)', '2 Dames (D16 et +)', '2 coureurs (D/H16 et +)', '1 coureur (D/H14 et +)'],
            'Parcours : 30′, 40′, 20′, 20′, 40′, 30′. Total : 180 min.',
            [
                ['Jeune', null, 14, 18, 30],
                ['Dame 1', 'D', 16, null, 40],
                ['Dame 2', 'D', 16, null, 20],
                ['Coureur 1', null, 16, null, 20],
                ['Coureur 2', null, 16, null, 40],
                ['Coureur 3', null, 14, null, 30],
            ]],
        ['relais_sprint', 'Relais-Sprint (4 coureurs)', 'relais_sprint', 4, true, 13,
            ['2 Dames et 2 Hommes', 'D/H14 et + requis', 'Ordre : Dame, Homme, Homme, Dame'],
            'Chaque relayeur : 12–15 min. Niveau orange (sprint urbain).',
            [
                ['Dame 1', 'D', 14, null, 14],
                ['Homme 1', 'H', 14, null, 14],
                ['Homme 2', 'H', 14, null, 14],
                ['Dame 2', 'D', 14, null, 14],
            ]],
        ['cne_hommes', 'CNE Hommes (7 coureurs)', 'cne', 7, false, 14,
            ['Tous H16 et +', 'Panachages d\'âge et de sexe autorisés (Dames acceptées en cat. Hommes)', 'Relais 1–2 de nuit, relais 3 mixte, relais 4–7 de jour'],
            'Total : 290 min. Nuit + jour. Niveau violet.',
            [
                ['Relayeur 1', null, 16, null, 40],
                ['Relayeur 2', null, 16, null, 40],
                ['Relayeur 3', null, 16, null, 50],
                ['Relayeur 4', null, 16, null, 30],
                ['Relayeur 5', null, 16, null, 50],
                ['Relayeur 6', null, 16, null, 30],
                ['Relayeur 7', null, 16, null, 50],
            ]],
        ['cne_dames', 'CNE Dames (5 coureurs)', 'cne', 5, false, 15,
            ['Toutes D16 et +', 'Relais 1 de nuit'],
            'Total : 170 min. Nuit + jour. Niveau violet.',
            [
                ['Relayeur 1', 'D', 16, null, 30],
                ['Relayeur 2', 'D', 16, null, 40],
                ['Relayeur 3', 'D', 16, null, 30],
                ['Relayeur 4', 'D', 16, null, 40],
                ['Relayeur 5', 'D', 16, null, 30],
            ]],
        ['cne_jeunes', 'CNE Jeunes (4 coureurs)', 'cne', 4, false, 16,
            ['D/H14 et D/H16 uniquement', 'Au moins 1 féminine dans l\'équipe'],
            'Total : 120 min. Niveau orange/jaune.',
            [
                ['Féminine', 'D', 14, 16, 30],
                ['Coureur 2', null, 14, 16, 30],
                ['Coureur 3', null, 14, 16, 30],
                ['Coureur 4', null, 14, 16, 30],
            ]],
        ['trophee_gueorgiou', 'Trophée Thierry Gueorgiou (4 coureurs)', 'trophees', 4, false, 17,
            ['D/H10 et + (9 ans et + au 31 déc.)', 'Relayeurs 1 et 3 : D/H14 et + (13 ans et +)', 'Classement spécial si uniquement D/H16 et moins'],
            'Parcours : 30′ (jaune), 20′ (bleu), 30′ (jaune), 20′ (bleu).',
            [
                ['Relayeur 1', null, 14, null, 30],
                ['Relayeur 2', null, 10, null, 20],
                ['Relayeur 3', null, 14, null, 30],
                ['Relayeur 4', null, 10, null, 20],
            ]],
        ['relais_couleur_vert', 'Open Vert (2 coureurs)', 'relais_couleur', 2, false, 18,
            ['D/H10 et +', 'Panachage clubs autorisé'],
            '20 min/relayeur. Niveau vert.',
            [
                ['Relayeur 1', null, null, null, null],
                ['Relayeur 2', null, null, null, null],
            ]],
        ['relais_couleur_bleu', 'Open Bleu (2 coureurs)', 'relais_couleur', 2, false, 19,
            ['D/H12 et +', 'Panachage clubs autorisé'],
            '25–30 min/relayeur. Niveau bleu.',
            [
                ['Relayeur 1', null, null, null, null],
                ['Relayeur 2', null, null, null, null],
            ]],
        ['relais_couleur_jaune', 'Open Jaune (2 coureurs)', 'relais_couleur', 2, false, 20,
            ['D/H14 et +', 'Panachage clubs autorisé'],
            '25–30 min/relayeur. Niveau jaune.',
            [
                ['Relayeur 1', null, null, null, null],
                ['Relayeur 2', null, null, null, null],
            ]],
        ['relais_couleur_orange', 'Open Orange (2 coureurs)', 'relais_couleur', 2, false, 21,
            ['D/H16 et +', 'Panachage clubs autorisé'],
            '30 min/relayeur. Niveau orange.',
            [
                ['Relayeur 1', null, null, null, null],
                ['Relayeur 2', null, null, null, null],
            ]],
        ['relais_couleur_violet', 'Open Violet (2 coureurs)', 'relais_couleur', 2, false, 22,
            ['D/H16 et +', 'Panachage clubs autorisé'],
            '30 min/relayeur. Niveau violet.',
            [
                ['Relayeur 1', null, null, null, null],
                ['Relayeur 2', null, null, null, null],
            ]],
    ];
}
