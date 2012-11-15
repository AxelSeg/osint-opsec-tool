#!/usr/bin/python

import urllib2

import opsecHeader


def writeLatestPost(author, body, link_id, comment_id, link_title, subreddit, epoch_time_found, permalink):
    sql = 'INSERT INTO reddit (author, body, link_id, comment_id, link_title, subreddit, epoch_time, permalink) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)'
    opsecHeader.cur.execute(sql, (author, body, link_id, comment_id, link_title, subreddit, epoch_time_found, permalink))
    opsecHeader.db.commit()


def getUsers():
    users = []
    sql = "SELECT author FROM reddit_users"
    opsecHeader.cur.execute(sql)
    for row in opsecHeader.cur.fetchall():
        users.append(row[0])
    return users


def getLatestUserEpoch(user):
    sql = "SELECT MAX(epoch_time) FROM reddit WHERE author = %s"
    opsecHeader.cur.execute(sql, (user))
    for row in opsecHeader.cur.fetchall():
        result = row[0]
    if result is None:
        result = 0
    return result


def getUserComments(user):
    #http://www.reddit.com/dev/api

    user = urllib2.quote(user)

    redditQueryString = 'http://www.reddit.com/user/' + user + '/overview.json'
    opsecHeader.queryWebsiteJSON("reddit", redditQueryString, opsecHeader.reddit_api_key)
    opsecHeader.writeLastCheckedTime('reddit')

    redditResults = opsecHeader.readResultsJSON('reddit')
    try:
        redditAllResults = redditResults['data']['children']
    except KeyError:
        redditAllResults = None
    epoch_time_existing = getLatestUserEpoch(user)

    if not redditAllResults:
        print "No results."
    else:
        for x in redditAllResults:
            epoch_time_found = str((x['data']['created_utc'])).encode('utf-8')[:-2]
            if int(epoch_time_found) > int(epoch_time_existing):
                try:
                    link_id = (x['data']['link_id']).encode('utf-8')[3:]
                except KeyError:
                    link_id = ''
                comment_id = (x['data']['id']).encode('utf-8')
                author = (x['data']['author']).encode('utf-8')
                try:
                    body = (x['data']['body']).encode('utf-8')
                except KeyError:
                    body = ''
                try:
                    link_title = (x['data']['link_title']).encode('utf-8')
                except:
                    link_title = ''
                subreddit = (x['data']['subreddit']).encode('utf-8')
                permalink = 'http://www.reddit.com/r/' + subreddit + '/comments/' + link_id + '/' + urllib2.quote(link_title) + '/' + comment_id
                writeLatestPost(author, body, link_id, comment_id, link_title, subreddit, epoch_time_found, permalink)

                keywords = opsecHeader.getUserKeywords(author, 'reddit')
                for keyword in keywords:
                    if keyword in body:
                        opsecHeader.sendEmail(keyword, "Reddit", author)
