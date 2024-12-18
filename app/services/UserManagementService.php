<?php
class UserManagementService
{
    static function changeEmails($user, ?string $newNoseEmail, string $newRealEmail, bool $gmailWarning = false)
    {
        $oldRealEmail = $user->real_email;
        $user->real_email = $newRealEmail;
        if ($newNoseEmail)
            $user->nose_email = $newNoseEmail;

        $family_members = [];

        if ($user->family?->id) {
            $family_members = em()->createQuery(
                "SELECT COUNT(u) FROM User u 
                WHERE u.family = :family_id 
                AND u.real_email = :real_email 
                AND u.id != :user_id"
            )
                ->setParameters([
                    "real_email" => $oldRealEmail,
                    "family_id" => $user->family->id,
                    "user_id" => $user->id
                ])
                ->getSingleScalarResult();
            if ($family_members) {
                Toast::info("Votre famille contient $family_members membres avec un email similaire !");
            }
        }
        em()->flush();
        Toast::success("Emails mis à jour");
        $gmailWarning && Toast::warning("Pensez à mettre à jour sur Gmail !");
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