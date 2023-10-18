<?php
use Ovh\Api;

class OvhClient implements OvhClientInterface
{

    private Api $api;
    private string $domain;

    function __construct(Api $api, string $domain = "nose42.fr")
    {
        $this->domain = $domain;
        $this->api = $api;
    }

    //Auth
    function logOut()
    {
        return $this->api->post('/auth/logout');
    }
    function logIn()
    {
        return $this->api->post('/auth/credential', [
            "accessRules" => [
                ["method" => "GET", "path" => "*"],
                ["method" => "POST", "path" => "*"],
                ["method" => "DELETE", "path" => "*"],
            ]
        ]);
    }
    function currentAuth()
    {
        return $this->api->get('/auth/currentCredential');
    }

    function getMailingLists()
    {
        return $this->api->get("/email/domain/$this->domain/mailingList");
    }
    function getMailingList($name)
    {
        return $this->api->get("/email/domain/$this->domain/mailingList/$name");
    }
    function getMailingListSubscribers($list)
    {
        return $this->api->get("/email/domain/$this->domain/mailingList/$list/subscriber");
    }
    function getMailingListSubscriber($list, $subscriberEmail)
    {
        return $this->api->get("/email/domain/$this->domain/mailingList/$list/subscriber?email=$subscriberEmail");
    }
    function addSubscriberToMailingList($list, $subscriberEmail)
    {
        return $this->api->post("/email/domain/$this->domain/mailingList/$list/subscriber", ["email" => $subscriberEmail]);
    }
    function removeSubscriberFromMailingList($list, $subscriberEmail)
    {
        return $this->api->delete("/email/domain/$this->domain/mailingList/$list/subscriber/$subscriberEmail");
    }

    // redirections
    function getRedirection($from = "", $to = "")
    {
        $query = $from || $to ? "?" . http_build_query(array_filter(["from" => $from, "to" => $to])) : "";
        return $this->api->get("/email/domain/$this->domain/redirection$query");
    }
    function addRedirection($from = "", $to = "")
    {
        return $this->api->post("/email/domain/$this->domain/redirection", ["from" => $from, "to" => $to]);
    }
    function removeRedirection($from = "", $to = "")
    {
        // maybe we want to store the redirection in the db somehow
        $redirection = $this->getRedirection($from, $to);
        if (count($redirection) > 1) {
            throw new Exception("More than one redirection from $from to $to");
        }
        return $this->api->delete("/email/domain/$this->domain/redirection/" . $redirection[0]);
    }
}