OSINT OPSEC TOOL
================


VERSION
=======

v1.0


LICENCE
=======

The OSINT OPSEC Tool is licenced under the GNU GPLv3
(See LICENCE)


INSTALLATION
============

Please see INSTALL


ACKNOWLEDGEMENTS
================

- @bogan for the original idea
- @pipesec and @metlstorm for suggestions during development
- Adrian Hayes for feedback on the 'con talk
- All of the kiwicon crue, sponsors and attendees where the tool was first 
demonstrated and released

eye.png logo source: https://commons.wikimedia.org/wiki/File:Black_Caodaist_symbol.PNG
"This work has been released into the public domain by its author, Nyo. This applies worldwide."


ABOUT
=====

The OSINT OPSEC Tool monitors multiple 21st Century OSINT sources 
real-time for keywords, then analyses the results, generates 
alerts, and maps trends of the data, finding all sorts of info people 
probably don't want others to see... 

Current monitered sites (Source | Native/Custom API | Authentication? | API Limits):
-  Twitter       |  native API  |  noauth w/ 1.0; need auth for 1.1   |    150 req/hour
-  Reddit        |  native API  |  auth through a unique User-Agent   |   1800 req/hour
-  Wordpress     |  native API  |  noauth                             |       ?
-  Facebook      |  native API  |  noauth yet; may be needed for user | 70,000 req/hour
-  Pastebin      |    custom    |  noauth                             |       ?
-  StackExchange |  native API  |  auth through API key               |    400 req/hour

Additionally the Google Maps API is used:

- GeoCode API    |  native API  |             noauth                  |  104 req/hour
- Maps API       |  native API  |              auth                   |       ?  


Each API is generally not queried more than once a minute to prevent throttling

- The OSINT OPSEC Tool backend is written in Python
- Data is stored in a MySQL Backend
- PHP is used for the frontend (plan to move Django to use Python throughout)
-> PHP generates HTML5/CSS3 that has been validated using W3C Valiation Services
-> The Javascript has also been validated with JSLint
- Main icons are 40px x 40px
- Google Maps icons are 25px x 25px 
- The webapp design follows the "Gutenberg rule"; 
  Primary viewing is top left; followed by far right; then bottom right; then bottom left
