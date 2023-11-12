<?php
use GuzzleHttp\Promise\PromiseInterface;

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
    function getRedirectionById($id);
    function addRedirection($from = "", $to = "");
    function removeRedirection($from = "", $to = "");
    //function changeRedirection(int $id, $to = "");


    // async
    function addRedirectionAsync($from, $to): PromiseInterface;
    function addSubscriberToMailingListAsync($list, $subscriberEmail): PromiseInterface;


    // redirection
    function getRedirectionAsync($from = "", $to = ""): PromiseInterface;
    function getMailingListSubscriberAsync($list, $subscriberEmail): PromiseInterface;
}