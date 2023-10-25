<?php

class OvhService
{
    // TODO :check if "nose" is the right name for the mailing list
    public static string $main_mailing_list = "nose";
    static function emailsSettingsValidation($user, $v_infos, $real_email, $nose_email)
    {
        $toast_message = "";
        try {
            // Check if user is part of a family
            if ($user->family->id && $user->family_leader) {
                // Get all family members with the same email
                $family_members = em()->getRepository(User::class)->findBy([
                    'family' => $user->family,
                    'real_email' => $user->real_email
                ]);
                // Update emails and redirection for every family member
                foreach ($family_members as $family_member) {
                    ovh_api()->removeRedirection($family_member->nose_email, $family_member->real_email);
                    $family_member->real_email = $real_email->value;
                    ovh_api()->addRedirection($family_member->nose_email, $real_email->value);
                    em()->persist($family_member);
                }
                $toast_message = count($family_members) > 1 ? "Emails mis à jour pour toute la famille !" : "Emails mis à jour !";
            } else {
                // Update emails and redirection for the user only
                ovh_api()->removeRedirection($user->nose_email, $user->real_email);
                $user->real_email = $real_email->value;
                $user->nose_email = $nose_email->value;
                ovh_api()->addRedirection($nose_email->value, $real_email->value);
                em()->persist($user);
                $toast_message = "Emails mis à jour !";
            }
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Erreur lors de l'ajout de la redirection", ["exception" => $e]);
            $v_infos->set_error("Erreur de redirection");
            return false;
        }
        em()->flush();
        Toast::create($toast_message);
        return true;
    }

    static function userDeactivateValidation($form, $user)
    {
        try {
            $mailingLists = ovh_api()->getMailingLists();
            foreach ($mailingLists as $list) {
                if (in_array($user->nose_email, ovh_api()->getMailingListSubscribers($list))) {
                    ovh_api()->removeSubscriberFromMailingList($list, $user->nose_email);
                    logger()->info("User {$user->id} removed from mailing list {$list})");
                }
            }
            ovh_api()->removeRedirection($user->nose_email, $user->real_email);
            logger()->info("User {$user->id} got his redirection {$user->nose_email} -> {$user->real_email} removed");
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Erreur dans la mise à jour des redirections ou des listes d'email", ["exception" => $e]);
            $form->set_error("Erreur dans la mise à jour des redirections ou des listes d'email");
            return false;
        }
        logger()->info("User {$user->id} deactivated by user " . User::getCurrent()->id);
        $user->status = UserStatus::DEACTIVATED;
        em()->flush();
        redirect("/licencies/desactive");
        return true;
    }

    static function userReactivateValidation($form, $users)
    {
        foreach ($users as $user) {
            try {
                ovh_api()->addRedirection($user->nose_email, $user->real_email);
                ovh_api()->addSubscriberToMailingList(self::$main_mailing_list, $user->real_email);
                logger()->info("User {$user->id} got his redirection {$user->nose_email} -> {$user->real_email} added");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Erreur dans la mise à jour des redirections.", ["exception" => $e]);
                $form->set_error("Erreur dans la mise à jour des redirections.");
                return false;
            }
            logger()->info("User {$user->id} reactivated by user " . User::getCurrent()->id);
            $user->status = UserStatus::INACTIVE;
            em()->persist($user);
        }
        em()->flush();
        return true;
    }

    static function userAddValidation($form, $user, $nose_email, $real_email)
    {
        $token = new AccessToken($user, AccessTokenType::ACTIVATE, new DateInterval('P2D'));
        try {
            ovh_api()->addRedirection($nose_email, $real_email->value);
            ovh_api()->addSubscriberToMailingList(self::$main_mailing_list, $real_email->value);
            logger()->info("New user got his redirection {$nose_email} -> {$real_email->value} added");
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Erreur dans la mise à jour des redirections.", ["exception" => $e]);
            $form->set_error("Erreur dans la mise à jour des redirections.");
            return false;
        }
        $result = MailerFactory::createActivationEmail($real_email->value, $token->id)->send();
        if ($result->success) {
            em()->persist($token);
            em()->persist($user);
            em()->flush();
            $form->set_success('Email envoyé!');
            logger()->info("User {$user->id} created and activation email sent");
        } else {
            logger()->warning("Atempt to create a user with email {$real_email->value} but activation email failed to send");
            $form->set_error("Erreur lors de l'envoi de l'email d'activation");
            ovh_api()->removeRedirection($nose_email, $real_email->value);
            ovh_api()->removeSubscriberFromMailingList(self::$main_mailing_list, $real_email->value);
            $form->set_error($result->message);
            return false;
        }
    }
}