<?php
interface OvhClientInterface
{
    // auth
    function logOut();
    function logIn();
    function currentAuth();

    // mailing lists
    function getMailingLists();
    function getMailingList($name);
    function getMailingListSubscribers($list);
    function getMailingListSubscriber($list, $subscriberEmail);
    function addSubscriberToMailingList($list, $subscriberEmail);
    function removeSubscriberFromMailingList($list, $subscriberEmail);

    // redirection
    function getRedirection($from = "", $to = "");
    function addRedirection($from = "", $to = "");
    function removeRedirection($from = "", $to = "");
}