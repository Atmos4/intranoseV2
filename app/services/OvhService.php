<?php
use GuzzleHttp\Promise\Utils;

class OvhService extends ServiceBase
{
    // TODO FIXME: remove
    public static string $main_mailing_list = "nose";

    private string $mailingList;
    private OvhClientInterface $ovhClient;

    function __construct(OvhClientInterface $ovhClient, string $mailingList = "nose")
    {
        $this->ovhClient = $ovhClient;
        $this->mailingList = $mailingList;
    }

    public static function getOvhClient(): OvhClientInterface
    {
        return self::getInstance()->ovhClient;
    }

    static function changeEmails(string $noseEmail, string $oldRealEmail, string $newRealEmail)
    {
        try {
            // ovh_api()->changeRedirection(etc...)
            self::getOvhClient()->removeRedirection($noseEmail, $oldRealEmail);
            self::getOvhClient()->addRedirection($noseEmail, $newRealEmail);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("API error", ["exception" => $e]);
            return false;
        }
        return true;
    }

    static function deactivateUser($user)
    {
        $emailCount = UserService::countUsersWithSameEmail($user->real_email);
        if (!$emailCount) {
            $mailingLists = self::getOvhClient()->getMailingLists();
            foreach ($mailingLists as $list) {
                if (self::getOvhClient()->getMailingListSubscriber($list, $user->real_email)) {
                    try {
                        self::getOvhClient()->removeSubscriberFromMailingList($list, $user->real_email);
                        logger()->info("User {$user->id} removed from mailing list {$list})");
                    } catch (GuzzleHttp\Exception\ClientException $e) {
                        logger()->error("Error with email list", ["exception" => $e]);
                        Toast::error("Erreur dans la mise à jour des listes d'email");
                        return false;
                    }
                }
            }
        } else {
            logger()->info("Skipping removing {email} from mailing list, {emailCount} users found with same email", [
                "email" => $user->real_email,
                "emailCount" => $emailCount,
            ]);
            Toast::info("Email utilisé, mailing non affecté");
        }
        try {
            self::getOvhClient()->removeRedirection($user->nose_email, $user->real_email);
            logger()->info("User {$user->id} got his redirection {$user->nose_email} -> {$user->real_email} removed");
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Error with redirections", ["exception" => $e]);
            Toast::error("Erreur dans la mise à jour des redirections");
            return false;
        }

        logger()->info("User {deactivatedUserId} deactivated by {mainUserId} ", [
            "deactivatedUserId" => $user->id,
            "mainUserId" => User::getMainUserId(),
        ]);
        Toast::success("Utilisateur désactivé");
        $user->status = UserStatus::DEACTIVATED;
        em()->flush();
        redirect("/licencies/desactive");
        return true;
    }



    /** @param User[] $users */
    static function reactivateUsers(array $users): bool
    {
        $promises = [];
        $emailCounts = [];
        foreach ($users as $user) {
            $promises["redirection_$user->id"] = self::getOvhClient()->addRedirectionAsync($user->nose_email, $user->real_email);

            $count = $emailCounts[$user->id] = UserService::countUsersWithSameEmail($user->real_email);
            if (!$count) {
                $promises["mailing_$user->id"] = self::getOvhClient()->addSubscriberToMailingListAsync(self::$main_mailing_list, $user->real_email);
            } else {
                Toast::info("Email utilisé, mailing non affecté");
            }
        }

        $responses = Utils::settle($promises)->wait();

        $anyError = false;

        foreach ($users as $user) {
            $redirectionResponse = $responses["redirection_$user->id"];
            if ($redirectionResponse['state'] === "rejected") {
                logger()->error(
                    "Error with redirection for {userId}",
                    ["exception" => $redirectionResponse, "userId" => $user->id]
                );
                Toast::error("Erreur: redirection");
                $anyError = true;
            }

            if (!$emailCounts[$user->id]) {
                $mailingResponse = $responses["mailing_$user->id"];
                if ($mailingResponse['state'] === "rejected") {
                    logger()->error(
                        "Error with mailing for {userId}",
                        ["exception" => $mailingResponse, "userId" => $user->id]
                    );
                    Toast::error("Erreur: mailing");
                    $anyError = true;
                }
            }
            $user->status = UserStatus::INACTIVE;
            logger()->info(
                "User reactivated",
                ["userId" => $user->id, "adminUserId" => User::getMainUserId()]
            );
            Toast::success("$user->first_name réactivé");
        }

        em()->flush();
        return $anyError;
    }

    static function updateUserInNoseMailingList(User $user, $action)
    {
        $client = self::getOvhClient();
        $mailingList = "nose";

        // The update takes about 15s on OVH's side. To prevent confusion we disable the action.
        switch ($action) {
            case "removeFromMailing":
                $client->removeSubscriberFromMailingList($mailingList, $user->real_email);
                $_SESSION['updatedMailingStatus'] = false;
                Toast::success("Retiré à la liste de diffusion");
                break;
            case "addToMailing":
                $client->addSubscriberToMailingList($mailingList, $user->real_email);
                $_SESSION['updatedMailingStatus'] = true;
                Toast::success("Ajouté de la liste de diffusion");
                break;
        }

        $realEmailIsSubscribed = $client->getMailingListSubscriber($mailingList, $user->real_email);

        if (isset($_SESSION['updatedMailingStatus'])) {
            if ($_SESSION['updatedMailingStatus'] !== !!$realEmailIsSubscribed) {
                return [$_SESSION['updatedMailingStatus'], true];
            } else {
                unset($_SESSION['updatedMailingStatus']);
            }
        }
        return [!!$realEmailIsSubscribed, false];

    }

    /** Add new user redirection and mailing list */
    static function addUser(string $nose_email, string $real_email)
    {
        $emailCount = UserService::countUsersWithSameEmail($real_email);
        $client = self::getOvhClient();
        if (!$client->getRedirection($nose_email, $real_email)) {
            try {
                $client->addRedirection($nose_email, $real_email);
                logger()->info("New user got his redirection {$nose_email} -> {$real_email} added");
                Toast::success("Redirection créée");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Error with redirections", ["exception" => $e]);
                Toast::error("Erreur dans la mise à jour des redirections.");
            }
        }
        if (!$client->getMailingListSubscriber(self::$main_mailing_list, $real_email) && !$emailCount) {
            try {
                $client->addSubscriberToMailingList(self::$main_mailing_list, $real_email);
                logger()->info("New user got his email {$real_email} added to mailing list " . self::$main_mailing_list);
                Toast::success("Utilisateur ajouté aux mailing lists");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Error when adding user with email {$real_email} to the list " . self::$main_mailing_list, ["exception" => $e]);
                Toast::error("Erreur dans la mise à jour des listes d'email.");
            }
        }

        if ($emailCount) {
            Toast::info("Email utilisé, mailing non affecté");
        }
    }
}