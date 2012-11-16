#!/usr/bin/python

import calendar
import email.utils
import time
import urllib2
from BeautifulSoup import BeautifulSoup

import opsecHeader


def writeLatestPost(account_id, user_id, site, content_type, epoch_time, profile_image, url, content, display_name):
    sql = "INSERT INTO stackexchange (account_id, user_id, site, content_type, epoch_time, profile_image, url, content, display_name) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"
    opsecHeader.cur.execute(sql, (account_id, user_id, site, content_type, epoch_time, profile_image, url, content, display_name))
    opsecHeader.db.commit()


def getLatestPost(user_id=None, site=None, content_type=None):
    epoch_time = '0'
    if user_id is None or site is None or content_type is None:
        return epoch_time
    elif user_id is not None and site is not None and content_type is not None:
        sql = "SELECT MAX(epoch_time) FROM `stackexchange` WHERE user_id = %s AND site = %s AND content_type = %s"
        opsecHeader.cur.execute(sql, (user_id, site, content_type))
    for row in opsecHeader.cur.fetchall():
        epoch_time = row[0]
        if(epoch_time < 1):
            epoch_time = '0'
    return epoch_time


def getUsers():
    users = []
    sql = "SELECT account_id FROM stackexchange_users"
    opsecHeader.cur.execute(sql)
    for row in opsecHeader.cur.fetchall():
        users.append(row[0])
    return users


def getUserAccounts(stackexchange_account):
    print("Getting StackExchange user accounts...")
    associatedQueryString = 'http://api.stackexchange.com/2.1/users/' + str(stackexchange_account) + '/associated?key=' + opsecHeader.stackexchange_api_key
    opsecHeader.queryWebsiteJSON("StackExchangeUserAccounts", associatedQueryString)

    results = opsecHeader.readResultsJSON('StackExchangeUserAccounts')
    items = results['items']

    # Set default accounts to 1; non-existant accounts
    stackoverflow_user_id = 1
    serverfault_user_id = 1

    for x in items:
        site_name = x['site_name']
        user_id = x['user_id']
        print site_name
        print user_id
        if (site_name == "Stack Overflow"):
            stackoverflow_user_id = user_id
        if (site_name == "Server Fault"):
            serverfault_user_id = user_id
        account_id = x['account_id']
        print x
    addAccounts(account_id, stackoverflow_user_id, serverfault_user_id)


def addAccounts(account_id, stackoverflow_user_id, serverfault_user_id):
    sql = "UPDATE stackexchange_users SET stackoverflow_user_id = %s, serverfault_user_id = %s WHERE account_id = %s"
    opsecHeader.cur.execute(sql, (stackoverflow_user_id, serverfault_user_id, account_id))
    opsecHeader.db.commit()


def getPost(account_id, site, user_id, content_type):
    latest_epoch_time = getLatestPost(user_id, site, content_type)
    queryString = 'http://api.stackexchange.com/2.1/users/' + str(user_id) + '/' + str(content_type) + 's?fromdate=' + str(latest_epoch_time) + '&order=desc&sort=creation&site=' + site + '&key=' + opsecHeader.stackexchange_api_key
    opsecHeader.queryWebsiteJSON(str(site) + str(user_id) + str(content_type), queryString)
    opsecHeader.writeLastCheckedTime('stackexchange')

    results = opsecHeader.readResultsJSON(str(site) + str(user_id) + str(content_type))
    items = results['items']
    for x in items:

        creation_date = x['creation_date']
        if(latest_epoch_time != creation_date):

            if(content_type == 'question'):
                question_id = x['question_id']
                url = x['link']
                html = urllib2.urlopen(url).read()
                soup = BeautifulSoup(html)
                dirty_content = soup.find('div', {'class': 'post-text', 'itemprop': 'description'})
                content = ''.join(dirty_content.findAll(text=True))

            elif(content_type == 'answer'):
                answer_id = x['answer_id']
                url = "http://" + str(site) + ".com/a/" + str(answer_id)
                html = urllib2.urlopen(url).read()
                soup = BeautifulSoup(html)
                answer_id = 'answer-' + str(answer_id)
                div_content = soup.find('div', {'id': answer_id})
                dirty_content = div_content.find('div', {'class': 'post-text'})
                content = ''.join(dirty_content.findAll(text=True))

            elif(content_type == 'comment'):
                comment_id = x['comment_id']
                post_id = x['post_id']
                short_url = 'http://' + str(site) + '.com/q/' + str(post_id)
                long_url = str(urllib2.urlopen(short_url).geturl())
                long_url = long_url.split("#")[0]
                url = long_url + '#comment' + str(comment_id) + '_' + str(post_id)
                html = urllib2.urlopen(url).read()
                soup = BeautifulSoup(html)
                comment_id_format = 'comment-' + str(comment_id)
                try: #Will fail if comments need to be loaded via AJAX
                    comment_tr = soup.find('tr', {'id': comment_id_format})
                    dirty_content = comment_tr.find('span', {'class': 'comment-copy'})
                    content = ''.join(dirty_content.findAll(text=True))
                except AttributeError:
                    content = 'See website'

            profile_image = x['owner']['profile_image']
            display_name = x['owner']['display_name']

            writeLatestPost(account_id, user_id, site, content_type, creation_date, profile_image, url, content, display_name)

            keywords = opsecHeader.getUserKeywords(account_id, 'stackexchange')
            for keyword in keywords:
                if keyword in content:
                    opsecHeader.sendEmail(keyword, "Stack Exchange", display_name)


def getUserPosts(account_id):

    user_id_sql = "SELECT stackoverflow_user_id, serverfault_user_id FROM stackexchange_users WHERE account_id = %s"
    opsecHeader.cur.execute(user_id_sql, (account_id))
    for row in opsecHeader.cur.fetchall():
        stackoverflow_user_id = row[0]
        serverfault_user_id = row[1]
    if ((stackoverflow_user_id == 0) or (serverfault_user_id == 0)):
	getUserAccounts(account_id)

    if (stackoverflow_user_id > 1):
        print("Checking stackoverflow")
        getPost(account_id, 'stackoverflow', stackoverflow_user_id, 'question')
        getPost(account_id, 'stackoverflow', stackoverflow_user_id, 'answer')
        getPost(account_id, 'stackoverflow', stackoverflow_user_id, 'comment')
    else:
        print("Account ID " + str(account_id) + " has no Stack Overflow account")
    if (serverfault_user_id > 1):
        print("Checking serverfault")
        getPost(account_id, 'serverfault', serverfault_user_id, 'question')
        getPost(account_id, 'serverfault', serverfault_user_id, 'answer')
        getPost(account_id, 'serverfault', serverfault_user_id, 'comment')
    else:
        print("Account ID " + str(account_id) + " has no Server Fault account")
