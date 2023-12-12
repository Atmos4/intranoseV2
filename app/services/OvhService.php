<?php
use GuzzleHttp\Promise\Utils;

class OvhService extends FactoryDependency
{
    private string $mailingList;
    private OvhClientInterface $ovhClient;

    function __construct(OvhClientInterface $ovhClient, string $mailingList = "nose")
    {
        $this->ovhClient = $ovhClient;
        $this->mailingList = $mailingList;
    }

    function changeEmails(string $noseEmail, string $oldRealEmail, string $newRealEmail)
    {
        try {
            // ovh_api()->changeRedirection(etc...)
            $this->ovhClient->removeRedirection($noseEmail, $oldRealEmail);
            $this->ovhClient->addRedirection($noseEmail, $newRealEmail);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("API error", ["exception" => $e]);
            return false;
        }
        return true;
    }

    function deactivateUser(User $user)
    {
        $emailCount = UserService::countUsersWithSameEmail($user->real_email);
        if (!$emailCount) {
            $mailingLists = $this->ovhClient->getMailingLists();
            foreach ($mailingLists as $list) {
                if ($this->ovhClient->getMailingListSubscriber($list, $user->real_email)) {
                    try {
                        $this->ovhClient->removeSubscriberFromMailingList($list, $user->real_email);
                        logger()->info("User {$user->login} removed from mailing list {$list})");
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
            $this->ovhClient->removeRedirection($user->nose_email, $user->real_email);
            logger()->info("User {$user->id} got his redirection {$user->nose_email} -> {$user->real_email} removed");
        } catch (GuzzleHttp\Exception\ClientException $e) {
            logger()->error("Error with redirections", ["exception" => $e]);
            Toast::error("Erreur dans la mise à jour des redirections");
            return false;
        }

        logger()->info("User {deactivatedUserLogin} deactivated by {mainUserId} ", [
            "deactivatedUserLogin" => $user->login,
            "mainUserId" => User::getMainUserId(),
        ]);
        Toast::success("Utilisateur désactivé");
        $user->status = UserStatus::DEACTIVATED;
        em()->flush();
        redirect("/licencies/desactive");
        return true;
    }



    /** @param User[] $users */
    function reactivateUsers(array $users): bool
    {
        $promises = [];
        $emailCounts = [];
        foreach ($users as $user) {
            $promises["redirection_$user->id"] = $this->ovhClient->addRedirectionAsync($user->nose_email, $user->real_email);

            $count = $emailCounts[$user->id] = UserService::countUsersWithSameEmail($user->real_email);
            if (!$count) {
                $promises["mailing_$user->id"] = $this->ovhClient->addSubscriberToMailingListAsync($this->mailingList, $user->real_email);
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
                    "Error with redirection for {login}",
                    ["exception" => $redirectionResponse, "login" => $user->login]
                );
                Toast::error("Erreur: redirection");
                $anyError = true;
            }

            if (!$emailCounts[$user->id]) {
                $mailingResponse = $responses["mailing_$user->login"];
                if ($mailingResponse['state'] === "rejected") {
                    logger()->error(
                        "Error with mailing for {login}",
                        ["exception" => $mailingResponse, "login" => $user->login]
                    );
                    Toast::error("Erreur: mailing");
                    $anyError = true;
                }
            }
            $user->status = UserStatus::INACTIVE;
            logger()->info(
                "User reactivated",
                ["login" => $user->login, "adminUserId" => User::getMainUserId()]
            );
            Toast::success("$user->first_name réactivé");
        }

        em()->flush();
        return $anyError;
    }

    function updateUserInNoseMailingList(User $user, $action)
    {
        $client = $this->ovhClient;

        // The update takes about 15s on OVH's side. To prevent confusion we disable the action.
        switch ($action) {
            case "removeFromMailing":
                $client->removeSubscriberFromMailingList($this->mailingList, $user->real_email);
                $_SESSION['updatedMailingStatus'][$user->real_email] = false;
                Toast::success("Retiré à la liste de diffusion");
                break;
            case "addToMailing":
                $client->addSubscriberToMailingList($this->mailingList, $user->real_email);
                $_SESSION['updatedMailingStatus'][$user->real_email] = true;
                Toast::success("Ajouté de la liste de diffusion");
                break;
        }

        $realEmailIsSubscribed = $client->getMailingListSubscriber($this->mailingList, $user->real_email);

        if (isset($_SESSION['updatedMailingStatus']) and isset($_SESSION['updatedMailingStatus'][$user->real_email])) {
            if ($_SESSION['updatedMailingStatus'][$user->real_email] !== !!$realEmailIsSubscribed) {
                return [$_SESSION['updatedMailingStatus'][$user->real_email], true];
            } else {
                unset($_SESSION['updatedMailingStatus'][$user->real_email]);
            }
        }
        return [!!$realEmailIsSubscribed, false];

    }

    /** Add new user redirection and mailing list */
    function addUser(string $nose_email, string $real_email)
    {
        $emailCount = UserService::countUsersWithSameEmail($real_email);
        $client = $this->ovhClient;
        if (!$client->getRedirection($nose_email, $real_email)) {
            try {
                $client->addRedirection($nose_email, $real_email);
                logger()->info("New user got his redirection {noseEmail} -> {realEmail} added", ["noseEmail" => $nose_email, "realEmail" => $real_email]);
                Toast::success("Redirection créée");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Error with redirections", ["exception" => $e]);
                Toast::error("Erreur dans la mise à jour des redirections.");
            }
        }
        if (!$client->getMailingListSubscriber($this->mailingList, $real_email) && !$emailCount) {
            try {
                $client->addSubscriberToMailingList($this->mailingList, $real_email);
                logger()->info("New user got his email {realEmail} added to mailing list {mailingList}", ["realEmail" => $real_email, "mailingList" => $this->mailingList]);
                Toast::success("Utilisateur ajouté aux mailing lists");
            } catch (GuzzleHttp\Exception\ClientException $e) {
                logger()->error("Error when adding user with email {realEmail} to the list {mailingList}", ["exception" => $e, "realEmail" => $real_email, "mailingList" => $this->mailingList]);
                Toast::error("Erreur dans la mise à jour des listes d'email.");
            }
        }

        if ($emailCount) {
            Toast::info("Email utilisé, mailing non affecté");
        }
    }
}