<?php

function get_user_login($id)
{
    return fetch("SELECT login FROM licencies WHERE id=? LIMIT 1;", $id)[0];
}

function change_password($post, $id)
{
    if (isset($post["password"])) {
        $currentPassword = $post['currentPassword'];
        $password = $post['password'];
        $passwordConfirm = $post['passwordConfirm'];
        if (strlen($password) < 6) {
            return ["Mot de passe trop court", "error"];
        }
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
        $newLogin = $post["newLogin"];
        if (strlen($newLogin) < 6) {
            return ["Trop court", "error"];
        }
        $test_pass = fetch(
            "SELECT id, login, perm 
            FROM licencies
            WHERE login = ? OR login = ?;",
            $login,
            $newLogin
        );
        if (count($test_pass) == 1) {
            if ($test_pass[0]['id'] == $_SESSION['user_id'] or check_auth("COACH")) {
                query_db(
                    "UPDATE licencies 
                    SET login =?
                    WHERE id = ? ;",
                    $newLogin,
                    $id
                );
                return ["Login mis à jour", "success"];
            }
        } elseif (count($test_pass) > 1) {
            return ["Déjà utilisé", "error"];
        }
        return ["Mauvais login", "error"];
    }
}