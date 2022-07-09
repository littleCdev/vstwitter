# vstwitter
(A similar project like TwitFix/vxTwitter) A simple php script to get better previews from twitter for telegram/discord.

## requirements
 - a server running php8.1
    - php curl
    - php mysql (optional)
    - php memcached (optional)
- mysql (optional)
- memcached (optional)

## how to use
simply replace any twitter.com url with vstwitter.com
```
https://vstwitter.com/[rest of the url]
```

## config
- **crendentials** api keys you get from the twitterdevelopment portal
- **usememcached** if set to true the given memcachedserver von **memcached** will be used as additional cache ontop of mysql, this is optional 
- **usemysql** if set to true the api reponses will be stored in the given mysql-server in **mysql** this is optional but recommended because the api-requests are limited to 900 per 15min

- **devmode** this is for development, if set to true you won't be forwarded to twitter and only greet with a message
- **forcebot** this is only for development and only works if **devmode** is set to true, if true every user-agent is accepted as a bot
- **subfolder** if you run this in a subfolder like myhost.org/vstwitter/ the given path will be removed from the url before forwarding to twitter

## how to run
- copy or rename the config-example.php to config.php
- add at least your api-keys to the **credentials** section
- if you want to use mysql setup the database first, you can easily do this with a small script
````
$cd src/utils
$php createdb.php
ok
$
````
- you're done :)


## other
projects this i'm using in this repo
- [ferrysyahrinal/twifer](https://github.com/ferrysyahrinal/twifer) for the twitter api
- [bramus/router](https://github.com/bramus/router) for routing
