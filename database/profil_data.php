<?php

function get_user_data()
{
    return fetch("SELECT * FROM licencies WHERE id = ? LIMIT 1;", $_SESSION['user_id'])[0];
}

function change_email($post, $id)
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
            $id
        );
        return ["Mails mis à jour !", "success"];
    }
}

function change_infos($post, $id)
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
            $id
        );
        return ["Infos mises à jour !", "success"];
    }
}

function change_password($post, $id)
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
            $id,
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
                    $id
                );
                return ["Mot de passe mis à jour", "success"];
            } else {
                return ["Nouveau mot de passe et confirmation différents", "error"];
            }
        } else {
            return ["Mauvais mot de passe", "error"];
        }
    }
}

function change_login($post, $id)
{
    if (isset($post["login"])) {
        $login = $post['login'];
        $password = $post['password'];
        $newLogin = $post["newLogin"];
        $test_pass = fetch(
            "SELECT 
        login, perm 
        FROM licencies
        WHERE login = ? AND password = MD5(?) LIMIT 1;",
            $login,
            $password
        );
        if (count($test_pass)) {
            query_db(
                "UPDATE licencies 
        SET 
            login =?
        WHERE
            id = ? ;",
                $newLogin,
                $id
            );
            return ["Login mis à jour !", "success"];
        } else {
            return ["Mauvais login/mot de passe", "error"];
        }
    }
}

function change_profil_data($post, $id)
{
    //Check from which form it is coming
    if (isset($post['submitEMail'])) {
        return change_email($post, $id);
    }
    if (isset($post['submitInfos'])) {
        return change_infos($post, $id);
    }
    if (isset($post['submitPassword'])) {
        return change_password($post, $id);
    }
    if (isset($post['submitLogin'])) {
        return change_login($post, $id);
    }
}
