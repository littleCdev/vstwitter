<?php

if(!defined('STDIN') ) {
    die("this is meant to be run from cli only!");
}


include "../../config.php";


$pdo = new PDO("mysql:host=" . $config["mysql"]["host"] . ";", "" . $config["mysql"]["user"] . "", "" . $config["mysql"]["password"] . "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->query("create database if not exists ".$config["mysql"]["database"].";");
$pdo->query("use ".$config["mysql"]["database"].";");

$tableQuery = file_get_contents("../sql/vstwitter.sql");
$pdo->query($tableQuery);

echo "ok\n";