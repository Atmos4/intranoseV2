<?php
function get_user_data()
{
    return fetch("SELECT * FROM licencies WHERE id = ? LIMIT 1;", $_SESSION['user_id'])[0];
}

function modify_user_data($post)
{
    //Function is not ready yet
    exit;

    if (isset($post['submitButton'])) { //Check it is coming from a form
        $prenom = $post["prenom"]; //set PHP variables like this so we can use them anywhere in code below
        $nom = $post["nom"];
        $sexe = $post["sexe"];
        $email = $post["email"];
        $emailnose = $post["emailnose"];
        $sportident = $post["sportident"];
        $adresse = $post["adresse"];
        $adresse2 = $post["adresse2"];
        $codepostal = $post["codePostal"];
        $ville = $post["ville"];
        $portable = $post["portable"];
        $fixe = $post["fixe"];

        query_db(
            "UPDATE licencies 
            SET 
                prenom = ?,
                nom = ?,
                sexe=?,
                realmail=?,
                email=?,
                sportident=?,
                adresse1=?,
                adresse2=?,
                cp=?,
                ville=?,
                tel=?,
                telport=?
            WHERE
                id = ?",
            $prenom,
            $nom,
            $sexe,
            $email,
            $emailnose,
            $sportident,
            $adresse,
            $adresse2,
            $codepostal,
            $ville,
            $fixe,
            $portable,
            $_SESSION['user_id']
        );
    }
}
