<?php
function handle_login($post)
{
    if (count($post) && (!empty($post['login']) || !empty($post['password']))) {
        $login = $post['login'];
        $password = $post['password'];

        $user_data = fetch(
            "SELECT id, perm
            FROM licencies
            WHERE login=? AND password=MD5(?) LIMIT 1;",
            $login,
            $password
        );
        if (count($user_data)) {
            $_SESSION['user_id'] = $user_data[0]['id'];
            $_SESSION['user_permission'] = $user_data[0]['perm'];

            redirect("accueil");
        } else {
            return "Utilisateur non trouvé";
        }
    }
}
