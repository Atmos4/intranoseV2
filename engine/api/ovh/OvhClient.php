<?php
use GuzzleHttp\Promise\PromiseInterface;

class OvhClient implements OvhClientInterface
{

    private OvhHttpClient $api;
    private string $domain;

    function __construct(OvhHttpClient $api, string $domain = "nose42.fr")
    {
        $this->domain = $domain;
        $this->api = $api;
    }

    //Auth
    function logOut()
    {
        return $this->api->send('POST', '/auth/logout');
    }
    function logIn()
    {
        return $this->api->send('POST', '/auth/credential', [
            "accessRules" => [
                ["method" => "GET", "path" => "*"],
                ["method" => "POST", "path" => "*"],
                ["method" => "DELETE", "path" => "*"],
            ]
        ]);
    }
    function currentAuth()
    {
        return $this->api->send('GET', '/auth/currentCredential');
    }

    function getMailingLists()
    {
        return $this->api->send('GET', "/email/domain/$this->domain/mailingList");
    }
    function getMailingList($name)
    {
        return $this->api->send('GET', "/email/domain/$this->domain/mailingList/$name");
    }
    function getMailingListSubscribers($list)
    {
        return $this->api->send('GET', "/email/domain/$this->domain/mailingList/$list/subscriber");
    }
    function getMailingListSubscriber($list, $subscriberEmail)
    {
        return $this->api->send('GET', "/email/domain/$this->domain/mailingList/$list/subscriber?email=$subscriberEmail");
    }
    function addSubscriberToMailingList($list, $subscriberEmail)
    {
        return $this->api->send('POST', "/email/domain/$this->domain/mailingList/$list/subscriber", ["email" => $subscriberEmail]);
    }
    function removeSubscriberFromMailingList($list, $subscriberEmail)
    {
        return $this->api->send('DELETE', "/email/domain/$this->domain/mailingList/$list/subscriber/$subscriberEmail");
    }

    // redirections
    function getRedirection($from = "", $to = "")
    {
        $query = $from || $to ? "?" . http_build_query(array_filter(["from" => $from, "to" => $to])) : "";
        return $this->api->send('GET', "/email/domain/$this->domain/redirection$query");
    }
    function addRedirection($from = "", $to = "")
    {
        return $this->api->send('POST', "/email/domain/$this->domain/redirection", ["from" => $from, "to" => $to, "localCopy" => false]);
    }
    function removeRedirection($from = "", $to = "")
    {
        // maybe we want to store the redirection in the db somehow
        $redirection = $this->getRedirection($from, $to);
        if (count($redirection) > 1) {
            throw new Exception("More than one redirection from $from to $to");
        }
        return $this->api->send('DELETE', "/email/domain/$this->domain/redirection/" . $redirection[0]);
    }
    function getRedirectionById($id)
    {
        return $this->api->send('GET', "/email/domain/$this->domain/redirection/$id");
    }

    // async
    function addRedirectionAsync($from, $to): PromiseInterface
    {
        return $this->api->sendAsync('POST', "/email/domain/$this->domain/redirection", ["from" => $from, "to" => $to, "localCopy" => false]);
    }

    function addSubscriberToMailingListAsync($list, $subscriberEmail): PromiseInterface
    {
        return $this->api->sendAsync('POST', "/email/domain/$this->domain/mailingList/$list/subscriber", ["email" => $subscriberEmail]);
    }

    function getRedirectionAsync($from = '', $to = ''): PromiseInterface
    {
        $query = $from || $to ? "?" . http_build_query(array_filter(["from" => $from, "to" => $to])) : "";
        return $this->api->sendAsync('GET', "/email/domain/$this->domain/redirection$query");
    }

    function getMailingListSubscriberAsync($list, $subscriberEmail): PromiseInterface
    {
        return $this->api->sendAsync('GET', "/email/domain/$this->domain/mailingList/$list/subscriber?email=$subscriberEmail");
    }
}