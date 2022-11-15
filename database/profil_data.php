<?php

function get_user_data()
{
    return fetch("SELECT * FROM licencies WHERE id = ? LIMIT 1;", $_SESSION['user_id'])[0];
}

function modify_email($post)
{
    if (isset($post["email"]) and isset($post["emailnose"])) {
        $email = $post["email"];
        $emailnose = $post["emailnose"];
        query_db(
            "UPDATE licencies 
        SET 
            realmail=?,
            email=?
        WHERE
            id = ? ;",
            $email,
            $emailnose,
            $_SESSION['user_id']
        );
        return ["Mails mis à jours !", "success"];
    }
}

function modify_infos($post)
{

    if (isset($post["sportident"])) {
        $sportident = $post["sportident"];
        $adresse = $post["adresse"];
        $adresse2 = $post["adresse2"];
        $codepostal = $post["codePostal"];
        $ville = $post["ville"];
        $fixe = $post["fixe"];
        $portable = $post["portable"];

        query_db(
            "UPDATE licencies 
            SET
                sportident = ?,
                adresse1=?,
                adresse2=?,
                cp=?,
                ville=?,
                tel=?,
                telport=?
            WHERE
                id = ?;",
            $sportident,
            $adresse,
            $adresse2,
            $codepostal,
            $ville,
            $fixe,
            $portable,
            $_SESSION['user_id']
        );
        return ["Infos mises à jour !", "success"];
    }
}

function modify_password($post)
{
    if (isset($post["password"])) {
        $currentPassword = $post['currentPassword'];
        $password = $post['password'];
        $passwordConfirm = $post['passwordConfirm'];
        $test_pass = fetch(
            "SELECT 
        id, perm 
        FROM licencies
        WHERE id = ? AND password=MD5(?) LIMIT 1;",
            $_SESSION['user_id'],
            $currentPassword
        );
        if (count($test_pass)) {
            if ($password == $passwordConfirm) {
                query_db(
                    "UPDATE licencies 
                SET 
                    password =MD5(?)
                WHERE
                    id = ? ;",
                    $password,
                    $_SESSION['user_id']
                );
                return ["Mot de passe mis à jour", "success"];
            } else {
                return ["Confirmez le même mot de passe", "error"];
            }
        } else {
            return ["Mauvais mot de passe", "error"];
        }
    }
}

function modify_profil_data($post)
{
    //Check from which form it is coming
    if (isset($post['submitEMail'])) {
        return modify_email($post);
    }
    if (isset($post['submitInfos'])) {
        return modify_infos($post);
    }
    if (isset($post['submitPassword'])) {
        return modify_password($post);
    }
}
