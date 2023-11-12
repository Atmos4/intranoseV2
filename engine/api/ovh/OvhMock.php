<?php
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;

class OvhMock implements OvhClientInterface
{
    //Auth
    function logOut()
    {
        return true;
    }
    function logIn()
    {
        return [
            "consumerKey" => "",
            "state" => "expired",
            "validationUrl" => "string"
        ];
    }
    function currentAuth()
    {
        return [
            "allowedIPs" => [
                "OVHMOCK"
            ],
            "applicationId" => 0,
            "creation" => "2023-10-23T07:34:56.497Z",
            "credentialId" => 0,
            "expiration" => "2023-10-23T07:34:56.497Z",
            "lastUse" => "2023-10-23T07:34:56.497Z",
            "ovhSupport" => false,
            "rules" => [
                [
                    "method" => "DELETE",
                    "path" => "string"
                ]
            ],
            "status" => "expired"
        ];
    }

    function getMailingLists()
    {
        return [
            "nose@nose42.fr"
        ];
    }
    function getMailingList($name)
    {
        return [
            "id" => 0,
            "language" => "fr",
            "name" => "string",
            "nbSubscribers" => 0,
            "nbSubscribersUpdateDate" => "2023-10-23T07:32:00.182Z",
            "options" => [
                "moderatorMessage" => false,
                "subscribeByModerator" => false,
                "usersPostOnly" => false
            ],
            "ownerEmail" => "string",
            "replyTo" => "string"
        ];
    }
    function getMailingListSubscribers($list)
    {
        return [
            "string"
        ];
    }
    function getMailingListSubscriber($list, $subscriberEmail)
    {
        return [
            "0" => "mock"
        ];
    }
    function addSubscriberToMailingList($list, $subscriberEmail)
    {
        logger()->debug("OvhMock: $subscriberEmail added to $list");
        return [
            "account" => "string",
            "action" => "add",
            "date" => "2023-10-23T07:26:34.987Z",
            "domain" => "string",
            "id" => 0,
            "language" => "fr"
        ];
    }
    function removeSubscriberFromMailingList($list, $subscriberEmail)
    {
        throw new GuzzleHttp\Exception\ClientException(
            "Error adding redirection",
            new GuzzleHttp\Psr7\Request('POST', 'http://example.com'),
            new GuzzleHttp\Psr7\Response(400, [], 'Bad Request')
        );
        /* return [
            "account" => "string",
            "action" => "delete",
            "date" => "2023-10-23T07:25:35.113Z",
            "domain" => "string",
            "id" => 0,
            "language" => "fr"
        ]; */
    }

    // redirections
    function getRedirection($from = "", $to = "")
    {
        // We check if the user exists in the DB.
        return em()
            ->createQuery("SELECT COUNT(u) FROM User u WHERE u.nose_email=:email")
            ->setParameter("email", $from)
            ->getSingleScalarResult();
    }
    function addRedirection($from = "", $to = "")
    {
        if ($this->checkFromEmail($to, "add")) {
            throw new GuzzleHttp\Exception\ClientException(
                "Error adding redirection",
                new GuzzleHttp\Psr7\Request('POST', 'http://example.com'),
                new GuzzleHttp\Psr7\Response(400, [], 'Bad Request')
            );
        } else {
            return [
                "account" => "string",
                "action" => "add",
                "date" => "2023-10-23T07:45:30.236Z",
                "domain" => "string",
                "id" => "string",
                "type" => "25g"
            ];
        }
    }
    function removeRedirection($from = "", $to = "")
    {
        return [
            "account" => "string",
            "action" => "delete",
            "date" => "2023-10-22T18:02:04.821Z",
            "domain" => "string",
            "id" => "string",
            "type" => "25g"
        ];
    }

    function checkFromEmail($email, $string)
    {
        // Helper function to test exceptions
        $parts = explode('@', $email);
        $firstPart = $parts[0];
        return strpos($firstPart, $string) === 0;
    }
    function getRedirectionById($id)
    {
        return "todo fixme $id";
    }

    function getMailingListSubscriberAsync($list, $subscriberEmail): PromiseInterface
    {
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }

    function getRedirectionAsync($from = '', $to = ''): PromiseInterface
    {
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }

    function addRedirectionAsync($from, $to): PromiseInterface
    {
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }

    function addSubscriberToMailingListAsync($list, $subscriberEmail): PromiseInterface
    {
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }
}