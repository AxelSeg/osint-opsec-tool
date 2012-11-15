#!/usr/bin/python

import datetime
import gzip
import json
import MySQLdb
import os
import smtplib
import socket
import sys
import time
import urllib2
from ConfigParser import SafeConfigParser
from StringIO import StringIO

############## READ CONFIG ################
if __name__ == '__main__':
    path = os.path.split(sys.argv[0])[0]
else:
    path = os.path.split(__file__)[0]

parser = SafeConfigParser()
parser.read(os.path.join(path, 'config.ini'))

receiver_email = parser.get('email', 'receiver_email').replace("'", "")
sender_email = parser.get('email', 'sender_email').replace("'", "")
email_pw = parser.get('email', 'email_pw').replace("'", "")
reddit_api_key = parser.get('reddit', 'reddit_api_key').replace("'", "")
stackexchange_api_key = parser.get('stackexchange', 'stackexchange_api_key').replace("'", "")

############# CONNECT TO DB ###############

db_host = parser.get('database', 'db_host').replace("'", "")
db_name = parser.get('database', 'db_name').replace("'", "")
db_user = parser.get('database', 'db_user').replace("'", "")
db_pw = parser.get('database', 'db_pw').replace("'", "")

db = MySQLdb.connect(host=db_host, db=db_name, user=db_user, passwd=db_pw)
cur = db.cursor()

################ METHODS ##################

def writeLastCheckedTime(source):
    now = int(time.mktime(time.localtime()))
    sql = "UPDATE last_checked SET last_checked = %s WHERE source = %s"
    cur.execute(sql, (now, source))
    db.commit()


def writeTempResults(website, results):
    workingFile = '/tmp/OPSEC.' + website
    f = open(workingFile, 'w')
    f.write(results)
    f.close()


def readResultsJSON(website):
    try:
        f = open('/tmp/OPSEC.' + website, 'r')
        return json.load(f)
    except IOError:
        print "Error opening file"
        return None


def queryWebsiteJSON(website, query, userAgent='Python-urllib/2.7'):
    print "\nQuerying " + website + "..."
    print query

    opener = urllib2.build_opener()
    opener.addheaders = [('User-agent', userAgent)]

    try:
        urlResults = opener.open(query)

        if urlResults.info().get('Content-Encoding') == 'gzip':
            buf = StringIO(urlResults.read())
            f = gzip.GzipFile(fileobj=buf)
            results = f.read()
        else:
            results = urlResults.read()

        print results
        urlResults.close()

        writeTempResults(website, results)
    except urllib2.HTTPError:
        print "Error fetching JSON"


def sendEmail(keyword, source, user=None):
    domain = str(socket.gethostname())
    subject = 'OSINT OPSEC Tool - Keyword Detected'
    body = "'" + keyword + "'" + " has been detected on " + source
    if user is not None:
        body += " for user '" + user + "'"
    body += ".\n\n https://" + domain
    msg = ("From: %s\r\nTo: %s\r\nSubject: %s\r\n\r\n%s"
        % (sender_email, receiver_email, subject, body))
    s = smtplib.SMTP('localhost')
    s.login(sender_email, email_pw)
    s.sendmail(sender_email, [receiver_email], msg)
    s.quit()


def getUserKeywords(user, source):
    keywords = []
    sql = "SELECT keyword FROM keywords WHERE user = %s AND source = %s"
    cur.execute(sql, (user, source))
    for row in cur.fetchall():
        keywords.append(row[0])
    return keywords
