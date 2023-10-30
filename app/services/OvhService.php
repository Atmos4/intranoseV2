<?php

class OvhService
{
    // TODO :check if "nose" is the right name for the mailing list
    public static string $main_mailing_list = "nose";
    static function changeEmails($user, $v_infos, $real_email, $nose_email)
    {
        $toast_message = "";
        try {
            // Check if user is part of a family and is family leader
            if ($user->family->id && $user->family_leader) {
                // Get all family members with the same email
                $family_members = em()->getRepository(User::class)->findBy([
                    'family' => $user->family,
                    'real_email' => $user->real_email
                ]);
                // Update emails and redirection for every family member - might take a quite long time
                foreach ($family_members as $family_member) {
                    ovh_api()->removeRedirection($family_member->nose_email, $family_member->real_email);
                    $family_member->real_email = $real_email->value;
                    ovh_api()->addRedirection($family_member->nose_email, $real_email->value);
                    em()->persist($family_member);
                }
                Toast::create(count($family_members) > 1 ? "Emails mis à jour pour toute la famille !" : "Emails mis à jour !");
            } else {
                // Update emails and redirection for the user only
                ovh_api()->removeRedirection($user->nose_email, $user->real_email);
                $user->real_email = $real_email->value;
                $user->nose_email = $nose_email->value;
                ovh_api()->addRedirection($nose_email->value, $real_email->value);
                em()->persist($user);
                Toast::create("Emails mis à jour !");
            }
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Erreur lors de l'ajout de la redirection", ["exception" => $e]);
            $v_infos->set_error("Erreur de redirection");
            return false;
        }
        em()->flush();
        return true;
    }

    static function deactivateUser($user)
    {
        $mailingLists = ovh_api()->getMailingLists();
        foreach ($mailingLists as $list) {
            if (in_array($user->nose_email, ovh_api()->getMailingListSubscribers($list))) {
                try {
                    ovh_api()->removeSubscriberFromMailingList($list, $user->nose_email);
                    logger()->info("User {$user->id} removed from mailing list {$list})");
                } catch (GuzzleHttp\Exception\ClientException $e) {
                    logger()->error("Error with email list", ["exception" => $e]);
                    Toast::create("Erreur dans la mise à jour des listes d'email", ToastLevel::ERROR);
                    return false;
                }
            }
        }
        try {
            ovh_api()->removeRedirection($user->nose_email, $user->real_email);
            logger()->info("User {$user->id} got his redirection {$user->nose_email} -> {$user->real_email} removed");
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Error with redirections", ["exception" => $e]);
            Toast::create("Erreur dans la mise à jour des redirections", ToastLevel::ERROR);
            return false;
        }
        //if an exeption is thrown, this part is never executed
        logger()->info("User {$user->id} deactivated by user " . User::getMain()->id);
        $user->status = UserStatus::DEACTIVATED;
        em()->flush();
        redirect("/licencies/desactive");
        return true;
    }

    static function reactivateUser($form, $users)
    {
        foreach ($users as $user) {
            try {
                ovh_api()->addRedirection($user->nose_email, $user->real_email);
                logger()->info("User {$user->id} got his redirection {$user->nose_email} -> {$user->real_email} added");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Error with redirections.", ["exception" => $e]);
                $form->set_error("Erreur dans la mise à jour des redirections.");
                return false;
            }
            try {
                ovh_api()->addSubscriberToMailingList(self::$main_mailing_list, $user->real_email);
                logger()->info("User {$user->id} added to the main mailing list");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Error with mailing list.", ["exception" => $e]);
                $form->set_error("Erreur dans l'ajout de l'utilisateur {$user->id} à la liste principale.");
                // if there is a problem with the mailing list, we may have the issue to have a user not reactivated, but with 
                // the redirection already added.
                return false;
            }
            logger()->info("User {$user->id} reactivated by user " . User::getMain()->id);
            $user->status = UserStatus::INACTIVE;
            em()->persist($user);
            //flush for each user to avoid losing all changes if an error occurs
            em()->flush();
        }
    }

    static function addUser($nose_email, $real_email)
    {
        if (!ovh_api()->getRedirection($nose_email, $real_email->value)) {
            try {
                ovh_api()->addRedirection($nose_email, $real_email->value);
                logger()->info("New user got his redirection {$nose_email} -> {$real_email->value} added");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Error with redirections", ["exception" => $e]);
                Toast::create("Erreur dans la mise à jour des redirections.", ToastLevel::ERROR);
            }
        }
        try {
            ovh_api()->addSubscriberToMailingList(self::$main_mailing_list, $real_email->value);
            logger()->info("New user got his email {$real_email->value} added to mailing list " . self::$main_mailing_list);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Error when adding user with email {$real_email->value} to the list " . self::$main_mailing_list, ["exception" => $e]);
            Toast::create("Erreur dans la mise à jour des listes d'email.", ToastLevel::ERROR);
        }

    }
}