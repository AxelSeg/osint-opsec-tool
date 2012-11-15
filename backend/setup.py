#!/usr/bin/python

import getpass
import MySQLdb
import os
from ConfigParser import SafeConfigParser

os.system('clear')

print "*** OSINT OPSEC TOOL DB Setup ***\n"

print "Setting up MySQL Databases..."

parser = SafeConfigParser()
parser.read('config.ini')

## DB SETUP ##

db_host = parser.get('database', 'db_host').replace("'", "")
db_name = parser.get('database', 'db_name').replace("'", "")
db_user = parser.get('database', 'db_user').replace("'", "")
db_pw = parser.get('database', 'db_pw').replace("'", "")

db = MySQLdb.connect(host=db_host, user=db_user, passwd=db_pw, db=db_name)
cur = db.cursor()

print "Creating new tables in " + db_name + "..."

facebook_sql = "CREATE TABLE facebook (id INT(11) AUTO_INCREMENT, user_id BIGINT(10) UNSIGNED NOT NULL, name VARCHAR(50) NOT NULL, message TEXT NOT NULL, updated_time VARCHAR(50) NOT NULL, keyword VARCHAR(50), lat decimal(11,7), lng decimal(11,7), epoch_time INT(11), profile_picture VARCHAR(255), PRIMARY KEY (id), FULLTEXT KEY `message` (`message`)) ENGINE=MyISAM"
cur.execute(facebook_sql)
db.commit()

keywords_sql = "CREATE TABLE `keywords` (`id` int(11) NOT NULL AUTO_INCREMENT,`keyword` varchar(50) NOT NULL, `source` varchar(25) DEFAULT NULL, `user` varchar(25) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(keywords_sql)
db.commit()

last_checked_sql = "CREATE TABLE last_checked (id INT(1) AUTO_INCREMENT, source VARCHAR(30) NOT NULL, last_checked INT(11) NOT NULL, PRIMARY KEY (id))"
cur.execute(last_checked_sql)
db.commit()

opsec_registration_tokens_sql = "CREATE TABLE `opsec_registration_tokens` (`id` smallint(6) NOT NULL AUTO_INCREMENT,`token` char(128) NOT NULL,`issued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(opsec_registration_tokens_sql)
db.commit()

opsec_users_sql = "CREATE TABLE `opsec_users` (`user` char(20) CHARACTER SET utf8 NOT NULL, `password_hashed` char(60) CHARACTER SET utf8 NOT NULL,`email` char(1) DEFAULT NULL, PRIMARY KEY (`user`)) ENGINE=InnoDB DEFAULT CHARSET=latin1"
cur.execute(opsec_users_sql)
db.commit()

default_user_sql = "INSERT INTO opsec_users (user, password_hashed) VALUES ('opsec', '$2y$12$1HQrxlWbhHvUZnT.KN1AKui8nrfOURAn6RS9tJnt9Xa/uXa/g3rUG')"
cur.execute(default_user_sql)
db.commit()

opsec_user_login_history = "CREATE TABLE `opsec_user_login_history` ( `id` int(10) unsigned NOT NULL AUTO_INCREMENT, `user` char(20) NOT NULL, `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(opsec_user_login_history)
db.commit()

pastebin_sql = "CREATE TABLE `pastebin` (`ID` varchar(20) NOT NULL,`epoch_time` INT(11) NOT NULL,`title` text NOT NULL,`paste` text NOT NULL,`pasteID` varchar(50) NOT NULL,`keyword` varchar(50) NOT NULL, FULLTEXT KEY `Paste` (`Paste`) ) ENGINE=MyISAM DEFAULT CHARSET=latin1"
cur.execute(pastebin_sql)
db.commit()

reddit_sql = "CREATE TABLE `reddit` ( `id` int(11) NOT NULL AUTO_INCREMENT, `author` varchar(50) NOT NULL, `link_id` varchar(255) DEFAULT NULL, `comment_id` varchar(255) DEFAULT NULL, `body` text, `link_title` varchar(255) DEFAULT NULL, `subreddit` varchar(255) DEFAULT NULL, `epoch_time` int(11) DEFAULT NULL, `permalink` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`), FULLTEXT KEY `body` (`body`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(reddit_sql)
db.commit()

reddit_users_sql = "CREATE TABLE `reddit_users` (`id` int(11) NOT NULL AUTO_INCREMENT,`author` varchar(50) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(reddit_users_sql)
db.commit()

stackexchange_sql = "CREATE TABLE `stackexchange` (`id` int(11) NOT NULL AUTO_INCREMENT,   `account_id` int(11) NOT NULL,`user_id` int(11) NOT NULL,`site` varchar(255) NOT NULL,`content_type` varchar(255) NOT NULL,`epoch_time` int(11) NOT NULL,`profile_image` varchar(255) NOT NULL,`url` varchar(255) NOT NULL,`content` text NOT NULL,`display_name` varchar(255) DEFAULT NULL,PRIMARY KEY (`id`),FULLTEXT KEY `content` (`content`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(stackexchange_sql)
db.commit()

stackexchange_users_sql = "CREATE TABLE `stackexchange_users` (`id` int(11) NOT NULL AUTO_INCREMENT,`account_id` int(11) NOT NULL,`stackoverflow_user_id` int(11) NOT NULL,`serverfault_user_id` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(stackexchange_users_sql)
db.commit()

twitter_sql = "CREATE TABLE `twitter` (`id` int(11) NOT NULL AUTO_INCREMENT,`twitter_id` bigint(20) unsigned NOT NULL,`from_user` varchar(50) NOT NULL,`text` varchar(140) NOT NULL,`created_at` varchar(50) NOT NULL,`keyword` varchar(50) DEFAULT NULL, `lat` decimal(11,7) DEFAULT NULL,`lng` decimal(11,7) DEFAULT NULL,`epoch_time` int(11) DEFAULT NULL,`profile_image_url_https` varchar(255) DEFAULT NULL,`location` varchar(255) DEFAULT NULL,PRIMARY KEY (`id`),FULLTEXT KEY `text` (`text`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;"
cur.execute(twitter_sql)
db.commit()

twitter_users_sql = "CREATE TABLE `twitter_users` (`id` int(11) NOT NULL AUTO_INCREMENT,`user` varchar(50) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;"
cur.execute(twitter_users_sql)
db.commit()

wordpress_sql = "CREATE TABLE `wordpress` (`id` int(11) NOT NULL AUTO_INCREMENT,`epoch_time` int(11) unsigned NOT NULL,`title` varchar(150) NOT NULL,`author` varchar(150) NOT NULL,`content` text NOT NULL,`keyword` varchar(50) DEFAULT NULL,`Link` varchar(255) DEFAULT NULL,PRIMARY KEY (`id`), FULLTEXT KEY `content` (`content`)) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1"
cur.execute(wordpress_sql)
db.commit()

for i in ("twitter", "reddit", "stackexchange", "facebook", "wordpress", "pastebin"):
    default_last_checked_sql = "INSERT INTO last_checked (source, last_checked) VALUES ('" + i + "', '0')"
    cur.execute(default_last_checked_sql)
    db.commit()
