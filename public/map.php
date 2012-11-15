<?php

/*

NOTE: This file MUST stay public to comply with Google's TOS regarding their 
MAP API:

https://developers.google.com/maps/terms

"9.1 Free, Public Accessibility to Your Maps API Implementation.

9.1.1 General Rules.

(a) Free Access (No Fees). Your Maps API Implementation must be generally 
accessible to users without charge and must not require a fee-based 
subscription or other fee-based restricted access. This rule applies to Your 
Content and any other content in your Maps API Implementation, whether Your 
Content or the other content is in existence now or is added later.

(b) Public Access (No Firewall). Your Maps API implementation must not operate 
(i) only behind a firewall; or (ii) only on an internal network (except during 
the development and testing phase); or (iii) in a closed community (for example, 
through invitation-only access)."

*/
require_once($_SERVER['DOCUMENT_ROOT'].'/config/db.php');

function generateMarkersTwitter(){

    $sth = $GLOBALS['dbh']->query ("SELECT from_user, lat, lng, text, profile_image_url_https FROM twitter GROUP BY from_user ORDER BY id DESC LIMIT 10");
    $count = 0;

    $markers = 'var markers =[';

    $profile_images = array();
 
    while ($row = $sth->fetch ())
    {
        $from_user = htmlspecialchars($row['from_user']);
        $lat       = htmlspecialchars($row['lat']);
        $lng       = htmlspecialchars($row['lng']);
        $text      = htmlspecialchars($row['text']);
        $profile_image_url_https = htmlspecialchars($row['profile_image_url_https']);     

        if (($lat != null) && ($lng != null) && (($lng != '0.0000000') || ($lat != '0.0000000') ) ){
            $markers .= "['Twitter','$from_user', $lat, $lng, '$profile_image_url_https'],";
        }        

        $count++;
    }
    $markers .= '];';
    return $markers;

}

$markers = generateMarkersTwitter();

$google_api_key = $config_array['google_api_key'];

?>
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=<? echo $google_api_key;?>&sensor=false"></script>
    <script>
    <? echo $markers;?> 

        function initialize() {
            var wellington = new google.maps.LatLng(-0.0000000, 180.0000000);
            var mapOptions = {
                zoom: 1,
                center: wellington,
                MapTypeId: google.maps.MapTypeId.ROADMAP
            }
            var map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);

            var infowindow = new google.maps.InfoWindow(), marker, i;
            for (i = 0; i < markers.length; i++) {  
                marker = new google.maps.Marker({
                    icon: new google.maps.MarkerImage(
		            markers[i][4],
                            new google.maps.Size(40, 40)
			    ),
                    position: new google.maps.LatLng(markers[i][2], markers[i][3]),
                    map: map
                });
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infowindow.setContent(markers[i][1]);
                    infowindow.setContent("<b>Source:</b> " + markers[i][0] + "</br><b>User: </b>" + markers[i][1] + "</br><b>Lat: </b>" + markers[i][2] + "</br><b>Lng: </b>" + markers[i][3] );

                    infowindow.open(map, marker);
                }
            })(marker, i));
            }

          }
    google.maps.event.addDomListener(window, 'load', initialize);
    </script>
    <div id="map"><div id="map_canvas"></div></div>
