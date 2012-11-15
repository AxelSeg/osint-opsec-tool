#!/usr/bin/python

import sys
import opsecHeader
import urllib2


def writeLatestWordpress(epoch_time, title, author, content, link, keyword):
    sql = "INSERT INTO wordpress (epoch_time, title, author, content, link, keyword) VALUES (%s, %s, %s, %s, %s, %s)"
    opsecHeader.cur.execute(sql, (epoch_time, title, author, content, link, keyword))
    opsecHeader.db.commit()


def getLatestWordpress():
    result = '0'
    opsecHeader.cur.execute("SELECT epoch_time FROM `wordpress` ORDER BY epoch_time desc LIMIT 1")
    for row in opsecHeader.cur.fetchall():
        result = row[0]
    return result


def searchWordpress(raw_keyword):
    keyword = urllib2.quote(raw_keyword)
    opsecHeader.writeLastCheckedTime('wordpress')

    ############### WORDPRESS ##################
    #
    # See http://en.search.wordpress.com/?q=obama&s=date&f=json
    #
    # Arguments:
    # q = keyword to search for
    # s = sort by; we want date; not relevance
    # f = format; we want JSON

    wordpressQueryString = 'http://en.search.wordpress.com/?q=' + keyword + '&s=date&f=json'

    opsecHeader.queryWebsiteJSON("wordpress", wordpressQueryString)

    wordpressLatestEpoch = getLatestWordpress()
    wordpressResults = opsecHeader.readResultsJSON('wordpress')
    epochTime = wordpressResults[0]['epoch_time']

    if str(wordpressLatestEpoch) == str(epochTime):
        print "No new blog posts since last query."
    else:
        for x in wordpressResults:
            epochTime = x['epoch_time']
            if int(wordpressLatestEpoch) < int(epochTime):
                title = (x['title']).encode('utf-8')
                author = (x['author']).encode('utf-8')
                content = (x['content']).encode('utf-8')
                link = (x['link']).encode('utf-8')
                writeLatestWordpress(epochTime, title, author, content, link, keyword)
                opsecHeader.sendEmail(keyword, "Wordpress")
