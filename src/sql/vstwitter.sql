CREATE TABLE IF NOT EXISTS vstwitter(
    `tweetid`           varchar(255)    NOT NULL unique primary key,
    `content`           text,
    `createdon`         timestamp /* maybe delete tweets older than a month? */
);