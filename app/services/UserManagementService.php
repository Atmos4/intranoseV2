<?php
class UserManagementService
{
    static function changeEmails($user, $v_infos, string $newNoseEmail, string $newRealEmail)
    {
        $oldRealEmail = $user->real_email;
        $user->real_email = $newRealEmail;
        $user->nose_email = $newNoseEmail;

        $family_members = [];

        if ($user->family?->id) {
            $family_members = em()->createQuery(
                "SELECT u FROM User u 
                WHERE u.family = :family_id 
                AND u.real_email = :real_email 
                AND u.id != :user_id"
            )
                ->setParameters([
                    "real_email" => $oldRealEmail,
                    "family_id" => $user->family->id,
                    "user_id" => $user->id
                ])
                ->getResult();
            if ($family_members) {
                Toast::info("Votre famille contient $family_members->count membres avec un email similaire !");
            }
        }
        em()->flush();
        Toast::create("Emails mis à jour !");
    }

    static function deactivateUser($user)
    {
    }

    static function reactivateUser($form, $users)
    {
    }

    static function addUser($nose_email, $real_email)
    {
    }
}