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
        logger()->debug("OvhMock: get all mailing lists");
        return [
            "nose@nose42.fr"
        ];
    }
    function getMailingList($name)
    {
        logger()->debug("OvhMock: get details for mailing list $name");
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
        logger()->debug("OvhMock: get all subscribers to {mailingList}", ["mailingList" => $list]);
        return [
            "string"
        ];
    }
    function getMailingListSubscriber($list, $subscriberEmail)
    {
        logger()->debug("OvhMock: get {subscriberEmail} in {list}", ["subscriberEmail" => $subscriberEmail, "list" => $list]);
        return [
            "0" => "mock"
        ];
    }
    function addSubscriberToMailingList($list, $subscriberEmail)
    {
        logger()->debug("OvhMock: {subscriberEmail} added to {list}", ["subscriberEmail" => $subscriberEmail, "list" => $list]);
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
        logger()->debug("OvhMock: $subscriberEmail removed from $list");
        return [
            "account" => "string",
            "action" => "delete",
            "date" => "2023-10-23T07:25:35.113Z",
            "domain" => "string",
            "id" => 0,
            "language" => "fr"
        ];
    }

    // redirections
    function getRedirection($from = "", $to = "")
    {
        logger()->debug("OvhMock: get redirection from {from} to {to}", ["from" => $from, "to" => $to]);
        return true;
    }
    function addRedirection($from = "", $to = "")
    {
        logger()->debug("OvhMock: add redirection from {from} to {to}", ["from" => $from, "to" => $to]);
        return [
            "account" => "string",
            "action" => "add",
            "date" => "2023-10-23T07:45:30.236Z",
            "domain" => "string",
            "id" => "string",
            "type" => "25g"
        ];
    }
    function removeRedirection($from = "", $to = "")
    {
        logger()->debug("OvhMock: removed redirection from {from} to {to}", ["from" => $from, "to" => $to]);
        return [
            "account" => "string",
            "action" => "delete",
            "date" => "2023-10-22T18:02:04.821Z",
            "domain" => "string",
            "id" => "string",
            "type" => "25g"
        ];
    }

    function getRedirectionById($id)
    {
        return "todo fixme $id";
    }

    function getMailingListSubscriberAsync($list, $subscriberEmail): PromiseInterface
    {
        logger()->debug("OvhMock: ASYNC get {subscriberEmail} in {list}", ["subscriberEmail" => $subscriberEmail, "list" => $list]);
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }

    function getRedirectionAsync($from = '', $to = ''): PromiseInterface
    {
        logger()->debug("OvhMock: ASYNC get redirection from {from} to {to}", ["from" => $from, "to" => $to]);
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }

    function addRedirectionAsync($from, $to): PromiseInterface
    {
        logger()->debug("OvhMock: ASYNC add redirection from {from} to {to}", ["from" => $from, "to" => $to]);
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }

    function addSubscriberToMailingListAsync($list, $subscriberEmail): PromiseInterface
    {
        logger()->debug("OvhMock: ASYNC add {subscriberEmail} in {list}", ["subscriberEmail" => $subscriberEmail, "list" => $list]);
        $promise = new Promise();
        $promise->resolve("OK");
        return $promise;
    }
}