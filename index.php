<?php

error_reporting(E_ALL);

require "config.php";
require "botheaders.php";

spl_autoload_register(function ($class) {
    $sClassName =  str_replace("\\", "/", $class);
    $file = dirname(__FILE__) . "/src/" . $sClassName . ".php";
    if (file_exists($file)) {
        include_once $file;
        return;
    }
    echo "\n not found: " . $file . "";
});


/**
 * gets the videourl from a tweet-id using the twitter v1 api since the api v2 does not return the videourl...
 */
function getVideoUrl(\Twifer\API $api, $tweetId): string
{
    // add tweet_mode=>extended in case extended_entites are missing
    $result = $api->request("GET", "statuses/show/" . $tweetId,["include_entities"=>true,"tweet_mode"=>"extended"]);
    $media = $result["extended_entities"]["media"][0];

    // -1 because for gif's the bitrate is set to 0
    $biggestBitrate = -1;
    $videourl = "";
    foreach ($media["video_info"]["variants"] as $variant) {
        if ($variant["content_type"] != "video/mp4")
            continue;
        if ($variant["bitrate"] > $biggestBitrate) {
            $biggestBitrate = $variant["bitrate"];
            $videourl = $variant["url"];
        }
    }

    return $videourl;
}


function generatePreview($tweetId) {
    global $knownbotheaders, $config;
    $useragent = strtolower($_SERVER["HTTP_USER_AGENT"]);

    $uri = $_SERVER['REQUEST_URI'];

    // but i'm in subfolder for development...
    if (isset($config["subfolder"]) && strlen($config["subfolder"]) > 0) {
        $uri = str_replace($config["subfolder"], "", $uri);
    }
    $twitterlink = "https://twitter.com" . $uri;

    /**
     * @var $isBot
     * is set to ture if useragent matches with a bot
     */
    $isBot = false;
    foreach ($knownbotheaders as $knownbotheader) {
        if (strtolower($knownbotheader) == $useragent) {
            $isBot = true;
            break;
        }
    }

    if ($config["devmode"] && $config["forcebot"]) {
        $isBot = true;
    }

    // if useragent is not a bot forward to twitter again
    if ($isBot == false) {
        if ($config["devmode"]) {
            echo "this would forward normally to: " . $twitterlink;
        } else {
            header("Location: " . $twitterlink);
        }
        return;
    }
    // now that we know it's a bot check first if we have data in our cache
    $result = \lc\twCache::retriveFromCache($tweetId);
    $tweetFoundInCache = true;

    if ($result == null) {
        $tweetFoundInCache = false;
        // if we are here the request comes for a bot
        $twConnection = new \Twifer\API(
            $config["credentials"]["consumer_key"],
            $config["credentials"]["consumer_secret"],
            $config["credentials"]["bearer_token"]
        );

        $ApiExpansions = "?expansions=attachments.media_keys,author_id&media.fields=type,url,height,width";

        $result = $twConnection->request("GET", "/2/tweets/" . $tweetId . $ApiExpansions);
    }
    
    //print_r($result);
    // check the type, text,photo or video
    $tweetType = "unknown";

    $appendSiteName = "";
    if (isset($result["errors"])) {
        //telegrambot doesn't like 404
        //http_response_code(404);
        $errortitle = strtolower( $result["errors"][0]["title"]);
        if(strpos($errortitle,"authorization")> -1){
            $tweetType = "private";
        }else{
            $tweetType = "error";
            $errordetail = $result["errors"][0]["detail"];    
        }
    } else if (!isset($result["data"]["attachments"])) {
        // text only
        $tweetType = "text";
    } else {
        if ($result["includes"]["media"][0]["type"] == "photo") {
            $tweetType = "photo";
            $photourl = $result["includes"]["media"][0]["url"];
            if (count($result["includes"]["media"]) > 1) {
                $appendSiteName = "1/" . count($result["includes"]["media"]) . " 📷";
            }
        }else{
            // video/animation
            $tweetType = "video";
            if (!isset($result["includes"]["media"][0]["url"])) {
                $videourl = getVideoUrl($twConnection, $tweetId);
                $result["includes"]["media"][0]["url"] = $videourl;
            }else{
                $videourl = $result["includes"]["media"][0]["url"];
            }
        }
    }

    if($tweetType == "private"){
        if ($config["devmode"]) {
            echo "this would forward normally to: " . $twitterlink;
        } else {
            header("Location: " . $twitterlink);
        }
    }elseif ($tweetType != "error") {
        $sitename = "vstwitter.com " . $appendSiteName;
        $title = $result["includes"]["users"][0]["name"] . " (@" . $result["includes"]["users"][0]["username"] . ")";
        $description = $result["data"]["text"];
    }

    include "ogtemplates/" . $tweetType . ".php";

    if (!$tweetFoundInCache) {
        \lc\twCache::insertIntoCache($tweetId, $result);
    }
}
/**
 * //example links from twitter
 * https://twitter.com/CincinnatiZoo/statuses/1478048570095845380 
 * https://twitter.com/CincinnatiZoo/status/1478048570095845380 
 * https://twitter.com/CincinnatiZoo/status/1478048570095845380/video/1
 * https://twitter.com/i/web/status/1478048570095845380
 */
$router = new \Bramus\Router\Router();
$router->get(".+/status/(\d+)", [], function ($id){generatePreview($id);});
$router->get(".+/statuses/(\d+)", [], function ($id){generatePreview($id);});
$router->get("i/web/status/(\d+)", [], function ($id){generatePreview($id);});

/**
 * forward to github to readme etc
 */
$router->get("/", [], function () use($config) {
    $url = "https://github.com/littleCdev/vstwitter";

    if ($config["devmode"]) {
        echo "this would forward normally to: " . $url;
    } else {
        header("Location: " . $url);
    }
});


/**
 * redict user to twitter since i don't know what they wanted to see
 */
$router->get(".*", [], function () use($config) {
    $uri = $_SERVER['REQUEST_URI'];

    if (isset($config["subfolder"]) && strlen($config["subfolder"]) > 0) {
        $uri = str_replace($config["subfolder"], "", $uri);
    }
    $twitterlink = "https://twitter.com" . $uri;

    if ($config["devmode"]) {
        echo "this would forward normally to: " . $twitterlink;
    } else {
        header("Location: " . $twitterlink);
    }
});

try{
    $router->run();
}catch(Exception $e){
    error_log($e->getMessage());
}
