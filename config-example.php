<?php

$config = [
    "usememcached" => true,
    // memached connection string
    "memcached" => [
        "host"      => "localhost",
        "port"      => 11211
    ],
    // if set to false you don't need an mysqlserver but it will increase the amount of api requests
    // this also disables memcached
    "usemysql" => true,
    // mysql for caching
    "mysql" => [
        "host"      => "localhost",
        "database"  => "vstwitter",
        "user"      => "root",
        "password"  => ""
    ],
    // twitter api-keys
    "credentials" => [
        //these are values that you can obtain from developer portal
        'consumer_key' => "Lapl..............", 
        'consumer_secret' => "Z8H...............", 
        'bearer_token' => "AAA.........................................................", 
    ],



    // if true you won't be forwarded to twitter
    "devmode" => false,
    // if set true and devmode equals true every useragent will be accpected as a bot
    "forcebot" => false,
    // if you run this in a subfolder the given path will be removed from the url before forwarding to https://twitter.com....
    "subfolder" => "",
];
