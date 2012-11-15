#!/usr/bin/python

import calendar
import email.utils
import time
import urllib2

import opsecHeader


def writeTweet(twitter_id, from_user, text, created_at, keyword, location, lat, lng, epoch_time, profile_image_url_https):
    sql = "INSERT INTO twitter (twitter_id, from_user, text, created_at, keyword, location, lat, lng, epoch_time, profile_image_url_https) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
    opsecHeader.cur.execute(sql, (twitter_id, from_user, text, created_at, keyword, location, lat, lng, epoch_time, profile_image_url_https))
    opsecHeader.db.commit()


def getLatestTweet(from_user=None, keyword=None):
    twitter_id, epoch_time = '0', '0' # Default values

    if from_user is None and keyword is not None:
        sql = "SELECT twitter_id, epoch_time FROM `twitter` WHERE keyword = %s ORDER BY twitter_id desc LIMIT 1"
        opsecHeader.cur.execute(sql, (keyword))
    elif from_user is not None and keyword is None:
        sql = "SELECT twitter_id, epoch_time FROM `twitter` WHERE from_user = %s ORDER BY twitter_id desc LIMIT 1"
        opsecHeader.cur.execute(sql, (from_user))
    else:
        return None, None

    for row in opsecHeader.cur.fetchall():
        twitter_id = row[0]
        epoch_time = row[1]

    return twitter_id, epoch_time


def getUsers():
    users = []
    sql = "SELECT user FROM twitter_users"
    opsecHeader.cur.execute(sql)
    for row in opsecHeader.cur.fetchall():
        users.append(row[0])
    return users


def genGeo(from_user):
    geoQueryString = 'https://api.twitter.com/1/users/show.json?screen_name=' + from_user

    opsecHeader.queryWebsiteJSON("twitterGeo", geoQueryString)

    results = opsecHeader.readResultsJSON('twitterGeo')
    location = (results['location']).encode('utf-8')

    if not location:
        return 'null', '0.0000000', '0.0000000'
    else:
        googleQueryString = 'http://maps.googleapis.com/maps/api/geocode/json?&address=' + urllib2.quote(location) + '&sensor=false'
        opsecHeader.queryWebsiteJSON("googleGeoCode", googleQueryString)

        googleResults = opsecHeader.readResultsJSON('googleGeoCode')
        googleAllResults = googleResults['results']

        if not googleAllResults:
            return location, '0.0000000', '0.0000000'
        else:
            for x in googleAllResults:
                lat = (x['geometry']['location']['lat'])
                lng = (x['geometry']['location']['lng'])
                return location, lat, lng


def getUserTweets(user):
    screen_name = urllib2.quote(user)
    opsecHeader.writeLastCheckedTime('twitter')

    # See https://dev.twitter.com/docs/api/1/get/statuses/user_timeline
    tweetSinceDate = str(getLatestTweet(screen_name, None)[0])
    epochTimeExisting = getLatestTweet(screen_name, None)[1]

    twitterQueryString = 'https://api.twitter.com/1/statuses/user_timeline.json?screen_name=' + screen_name + '&count=10'

    if tweetSinceDate != '0': # Twitter does not play nice with invalid since_id's
        twitterQueryString += '&since_id=' + tweetSinceDate

    opsecHeader.queryWebsiteJSON("twitterUserTweets", twitterQueryString)

    twitterResults = opsecHeader.readResultsJSON('twitterUserTweets')
    if twitterResults is not None:
        twitterAllResults = twitterResults
    else:
        twitterAllResults = None

    if not twitterAllResults:
        print "No results."
    else:
        for x in twitterAllResults:
            created_at = (x['created_at']).encode('utf-8')
            epochTimeFound = calendar.timegm((email.utils.parsedate(created_at)))
            if int(epochTimeFound) > int(epochTimeExisting):
                twitterID = (x['id'])
                text = (x['text']).encode('utf-8')
                from_user = (x['user']['screen_name']).encode('utf-8')
                created_at = (x['created_at']).encode('utf-8')
                profile_image_url_https = (x['user']['profile_image_url_https']).encode('utf-8')
                location, lat, lng = genGeo(from_user)

                writeTweet(twitterID, from_user, text, created_at, '', location, lat, lng, epochTimeFound, profile_image_url_https)
                keywords = opsecHeader.getUserKeywords(from_user, 'twitter')
                for keyword in keywords:
                    if keyword in text:
                        opsecHeader.sendEmail(keyword, "Twitter", from_user)


def searchTwitter(raw_keyword):
    keyword = urllib2.quote(raw_keyword)
    opsecHeader.writeLastCheckedTime('twitter')

    # See https://dev.twitter.com/docs/api/1/get/search
    tweetSinceDate = str(getLatestTweet(None, keyword)[0])
    searchQueryString = 'http://search.twitter.com/search.json?q=' + keyword + '&rpp=10&result_type=recent'

    if tweetSinceDate != '0': # Twitter does not play nice with invalid since_id's
        searchQueryString += '&since_id=' + tweetSinceDate

    opsecHeader.queryWebsiteJSON("twitter", searchQueryString)

    twitterResults = opsecHeader.readResultsJSON('twitter')
    twitterAllResults = twitterResults['results']

    if not twitterAllResults:
        print "No results."
    else:
        existingEpochTime = getLatestTweet(None, keyword)[1]

        for x in twitterAllResults:
            created_at = (x['created_at']).encode('utf-8')
            epochTimeFound = calendar.timegm((time.strptime(created_at, '%a, %d %b %Y %H:%M:%S +0000')))
            if int(epochTimeFound) > int(existingEpochTime):
                twitterID = (x['id'])
                from_user = (x['from_user']).encode('utf-8')
                text = (x['text']).encode('utf-8')
                created_at = (x['created_at']).encode('utf-8')
                profile_image_url_https = (x['profile_image_url_https']).encode('utf-8')
                location, lat, lng = genGeo(from_user)

                writeTweet(twitterID, from_user, text, created_at, keyword, location, lat, lng, epochTimeFound, profile_image_url_https)
                opsecHeader.sendEmail(keyword, "Twitter")
