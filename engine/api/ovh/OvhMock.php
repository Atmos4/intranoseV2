<?php
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
            "domain" => "string",
            "email" => "string",
            "mailinglist" => "string"
        ];
    }
    function addSubscriberToMailingList($list, $subscriberEmail)
    {
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
        return ["string"];
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
        // Helper function to test exeptions
        $parts = explode('@', $email);
        $firstPart = $parts[0];
        return strpos($firstPart, $string) === 0;
    }
}