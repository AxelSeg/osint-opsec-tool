#!/usr/bin/python

# This script is a modified version of the "scrapePastebinMySQL.py" script from 
# AndrewMohawk.com's PasteLert v2
#
# ---- EXTRACTED FROM ORIGINAL HEADER ---- 
# Parts stolen from @shellguardians pastebin.py script - Thanks!
# Do whatever you want with it -shrug-
# Andrew MacPherson
# @andrewmohawk
# http://www.andrewmohawk.com/
# ----- END EXTRACTED HEADER ---- 

import datetime
import re
import random
import sys
import time
import urllib2
from datetime import timedelta, date

import opsecHeader

pasteIDsfound = []
pasteMaxSize = 1000


def addPaste(title, id, paste):
    keywords = opsecHeader.getUserKeywords('all', 'pastebin')
    for keyword in keywords:
        if keyword in paste:
            now = int(time.mktime(time.localtime()))
            sql = "INSERT INTO `pastebin` (`epoch_time`, `title`, `paste`, `pasteID`, `keyword`) VALUES (%s, %s, %s, %s, %s)"
            try:
                if(opsecHeader.cur.execute(sql, (now, title, paste, id, keyword))):
                    opsecHeader.db.commit()
                    print "[+] Added."
            except:
                print '''[!] DB Problem (id:%s) NOT inserted''' % (id)
                print sys.exc_info()[0]
                return False
            opsecHeader.sendEmail(keyword, "Pastebin")


def getPastes():
    global pasteIDsfound, pasteMaxSize

    if(len(pasteIDsfound) >= (pasteMaxSize * 2)):
        print "[-] cleaning list"
        for i in range(0, len(pasteIDsfound) - (pasteMaxSize)):
            pasteIDsfound.pop(0)
    print "[-] Pulling archive list..."
    try:
        page = urllib2.urlopen("http://www.pastebin.com/archive.php").read()
        regex = re.compile('<td><img src="/i/t.gif" .*?<a href="/(.*?)">(.*?)</a></td>.*?<td>(.*?)</td>', re.S)
        pastes = regex.findall(page)
        for p in pastes:
            pasteID = p[0]
            pasteTitle = p[1]
            fetchAttempt = 0
            opsecHeader.writeLastCheckedTime('pastebin')
            if(pasteID not in pasteIDsfound):
                print "[-] New paste(", pasteID, ")"
                pasteIDsfound.append(pasteID)
                print len(pasteIDsfound)
                pastePage = ''
                fetchAttempts = 0
                while (pastePage == ''):
                    print "[+] Pulling Raw paste"
                    sock = urllib2.urlopen("http://pastebin.com/raw.php?i=" + pasteID)
                    pastePage = sock.read()
                    encoding = sock.headers['Content-type'].split('charset=')[1] # iso-8859-1
                    try:
                        pastePage = pastePage.decode(encoding).encode('utf-8')
                        if(pastePage == ''):
                            pastePage = 'empty paste from http://pastebin.com/raw.php?i=' + pasteID
                        if "requesting a little bit too much" in pastePage:
                            pastePage = ''
                            print "[-] hitting pastebin too quickly, sleeping for 2 seconds and trying again.."
                            time.sleep(2)
                    except:
                        print "[!] couldnt decode page to utf-8"
                    print "[-] Sleeping for 1 second"
                    time.sleep(1)
                    fetchAttempt = fetchAttempt + 1
                    if(fetchAttempt > 1):
                        print "[+] Couldnt fetch " + "http://pastebin.com/raw.php?i=" + pasteID + " after 2 tries"
                        pastePage = '  '
                addPaste(pasteTitle, pasteID, pastePage)
            else:
                print "[-] Already seen ", pasteID
        sleeptime = random.randint(15, 45)
        print "[-] sleeping for", sleeptime, "seconds.."
        time.sleep(sleeptime)
        return 1
    except IOError:
        print "[!] Error fetching list of pastes, sleeping for 10 seconds and trying again"
        time.sleep(10)
        return 0


def main():
    while True:
        getPastes()


if __name__ == "__main__":
    sys.exit(main())
