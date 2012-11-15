#!/usr/bin/python

import calendar
import time
import urllib2

import opsecHeader


def writeLatestPost(name, user_id, message, profile_picture, updated_time, keyword, epoch_time):
    sql = "INSERT INTO facebook (name, user_id, message, profile_picture, updated_time, keyword, epoch_time) VALUES (%s, %s, %s, %s, %s, %s, %s)"
    opsecHeader.cur.execute(sql, (name, user_id, message, profile_picture, updated_time, keyword, epoch_time))
    opsecHeader.db.commit()


def getLatestPostTime():
    result = '0'
    opsecHeader.cur.execute("SELECT MAX(epoch_time) FROM `facebook`")
    for row in opsecHeader.cur.fetchall():
        result = row[0]
    return result


def getProfilePicture(user_id):
    profilePictureString = 'https://graph.facebook.com/' + user_id + '/picture'
    urlResult = urllib2.urlopen(profilePictureString)
    result = urlResult.geturl().encode('utf-8')
    urlResult.close()
    return result


def searchFacebook(raw_keyword):
    opsecHeader.writeLastCheckedTime('facebook')
    keyword = urllib2.quote(raw_keyword)
    # See https://developers.facebook.com/docs/reference/api/
    #
    # Arguments:
    # q = keyword we are searching for
    # type = kind of object we are searching for e.g post
    #
    # Returns:
    # name; id (facebook.com/id for their profile)

    facebookLatestEpoch = getLatestPostTime()
    facebookQueryString = 'https://graph.facebook.com/search?q=' + keyword + '&type=post'
    opsecHeader.queryWebsiteJSON("facebook", facebookQueryString)

    print "Parsing Facebook data..."

    facebookResults = opsecHeader.readResultsJSON('facebook')
    facebookAllResults = facebookResults['data']

    if facebookAllResults:
        for x in facebookAllResults:
            if 'message' in x:
                message = x['message'].encode('utf-8')
                name = (x['from']['name']).encode('utf-8')
                user_id = (x['from']['id']).encode('utf-8')
                updated_time = (x['updated_time']).encode('utf-8')
                epoch_time = calendar.timegm((time.strptime(updated_time, '%Y-%m-%dT%H:%M:%S+0000')))

                if int(epoch_time) > int(facebookLatestEpoch):
                    profilePicture = getProfilePicture(user_id)
                    writeLatestPost(name, user_id, message, profilePicture, updated_time, keyword, epoch_time)
                    opsecHeader.sendEmail(keyword, "Facebook")
                    print "Updated Time: " + updated_time
                else:
                    print "Post too old."
