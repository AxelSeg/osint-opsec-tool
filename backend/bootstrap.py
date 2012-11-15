#!/usr/bin/python

import datetime

import opsecHeader
import twitter
import reddit
import stackexchange
import facebook
import wordpress

minute = datetime.datetime.now().minute
fiveMinInterval = (int(minute) / 5)-1
oneDigitMinute = int(str(minute)[-1])

print("################################")
print("#### OPSEC Search Bootstrap ####")
print("################################")

print("----- User Specific Search -----")
print("Attempting site/user specific search")

# Twitter
try:
    user = twitter.getUsers()[oneDigitMinute]
    twitter.getUserTweets(user)
except IndexError:
    print("No Twitter user found at index " + str(oneDigitMinute))

# Reddit
try:
    author = reddit.getUsers()[oneDigitMinute]
    reddit.getUserComments(author)
except IndexError:
    print("No Reddit user found at index " + str(oneDigitMinute))

# StackExchange
try:
    account_id = stackexchange.getUsers()[oneDigitMinute]
    stackexchange.getUserPosts(account_id)
except IndexError:
    print("No StackExchange user found at index " + str(oneDigitMinute))


print("-------- General search --------")
if (minute % 5) == 0:
    print("Attempting general site search...")
    try:
        keyword = opsecHeader.getUserKeywords('all', 'twitter')[fiveMinInterval]
        twitter.searchTwitter(keyword)
    except IndexError:
        print("No twitter keyword at index " + str(fiveMinInterval))

    try:
        keyword = opsecHeader.getUserKeywords('all', 'facebook')[fiveMinInterval]
        facebook.searchFacebook(keyword)
    except IndexError:
        print("No facebook keyword at index " + str(fiveMinInterval))

    try:
        keyword = opsecHeader.getUserKeywords('all', 'wordpress')[fiveMinInterval]
        wordpress.searchWordpress(keyword)
    except IndexError:
        print("No wordpress keyword at index " + str(fiveMinInterval))

else:
    print("Minute not a multiple of 5, not attempting general site search...")
