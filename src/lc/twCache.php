<?php

namespace lc;

use Exception;
use \PDO;
use \Memcached;
class twCache
{

    /**
     * tries to get an tweet from cache, returns null if not existing
     */
    public static function retriveFromCache(string $tweetid): mixed
    {
        global $config;
        if (!$config["usemysql"]) {
            return null;
        }

        if ($config["usememcached"]) {
            $memcached = new Memcached();
            $memcached->addServer($config["memcached"]["host"], $config["memcached"]["port"]);

            $result = $memcached->get($tweetid);

            if ($result != null) {
                return $result;
            }
        }

        try {
            $pdo = new PDO("mysql:host=" . $config["mysql"]["host"] . ";dbname=" . $config["mysql"]["database"] . "", "" . $config["mysql"]["user"] . "", "" . $config["mysql"]["password"] . "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            return null;
        }

        $stm = $pdo->prepare("select * from vstwitter where tweetid=:TID");
        $stm->execute([":TID" => $tweetid]);

        if ($stm->rowCount() == 0)
            return null;

        $row = $stm->fetchObject();

        $ret = unserialize($row->content);

        self::insertIntoMemcache($tweetid,$ret);

        return $ret;
    }

    private static function insertIntoMemcache(string $tweetid,mixed $tweet):void{
        global $config;
        if ($config["usememcached"]) {
            $memcached = new Memcached();
            $memcached->addServer($config["memcached"]["host"], $config["memcached"]["port"]);

            $memcached->set($tweetid, $tweet);
        }
    }

    /**
     * inserts tweet into cache
     */
    public static function insertIntoCache(string $tweetid, mixed $tweet): void
    {
        global $config;
        if (!$config["usemysql"]) {
            return;
        }
        
        $pdo = new PDO("mysql:host=" . $config["mysql"]["host"] . ";dbname=" . $config["mysql"]["database"] . "", "" . $config["mysql"]["user"] . "", "" . $config["mysql"]["password"] . "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stm = $pdo->prepare("insert into vstwitter (tweetid,content,createdon) VALUE (:TID,:TWEET,CURRENT_TIMESTAMP)");
        $stm->execute([":TID" => $tweetid, ":TWEET" => serialize($tweet)]);

        self::insertIntoMemcache($tweetid,$tweet);
    }
}
