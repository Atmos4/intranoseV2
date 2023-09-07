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
        return [];
    }
    function currentAuth()
    {
        return "You are currently on the OVH Mock. Logging out is useless. All data here is fake! :)";
    }

    function getMailingLists()
    {
        return ["mockedMail"];
    }
    function getMailingList($name)
    {
        return "Mocked mailing list";
    }
    function getMailingListSubscribers($list)
    {
        return [
            "jean.daniel@nose42.fr",
            "jean.eude@nose42.fr",
            "jean.michel@nose42.fr",
            "jean.francois@nose42.fr",
        ];
    }
    function getMailingListSubscriber($list, $subscriberEmail)
    {
        return [];
    }
    function addSubscriberToMailingList($list, $subscriberEmail)
    {
        return true;
    }
    function removeSubscriberFromMailingList($list, $subscriberEmail)
    {
        return "cheh";
    }

    // redirections
    function getRedirection($from = "", $to = "")
    {
        return ["6798"];
    }
    function addRedirection($from = "", $to = "")
    {
        return "ok bruv";
    }
    function removeRedirection($from = "", $to = "")
    {
        return "on t'aimait pas de toute facon";
    }
}