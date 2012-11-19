<?php

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){

    header('Location: index.php');

}
else{

require_once($_SERVER['DOCUMENT_ROOT'].'/config/db.php');

function check_strong_password($candidate_password) {
    $case1='/[A-Z]/';  // Uppercase
    $case2='/[a-z]/';  // Lowercase
    $case3='/[!@#$%^&*()-_=+{};:,<.>]/';  // Special chars
    $case4='/[0-9]/';  // Numbers

    if(preg_match_all($case1, $candidate_password, $o) < 1){ 
        echo "Password requires at least 1 uppercase chacter.";
	return false;
    }

    if(preg_match_all($case2, $candidate_password, $o) < 1){
        echo "Password must contain at least 1 lower case character.";
        return false;
    }

    if(preg_match_all($case3, $candidate_password, $o) < 1){
        echo "Password must contain at least 1 special character.";
	return false;
    }

    if(preg_match_all($case4, $candidate_password, $o) < 1){
        echo "Password must contain at least 1 number.";
        return false;
    }

    if(strlen($candidate_password)< 10) {
        echo "Entered password must be at least 10 characters long.";
        return false;
    }
    
    return true;
}

function getLatestHit($column = "source"){

    $sql = "SELECT 'twitter' AS table_name, epoch_time FROM twitter
            UNION ALL
            SELECT 'reddit' AS table_name, epoch_time FROM reddit
            UNION ALL
	    SELECT 'stackexchange' AS table_name, epoch_time FROM stackexchange
            UNION ALL
	    SELECT 'facebook' AS table_name, epoch_time FROM facebook
            UNION ALL
            SELECT 'pastebin' AS table_name, epoch_time FROM pastebin
            UNION ALL
	    SELECT 'wordpress' AS table_name, epoch_time FROM wordpress
            ORDER BY epoch_time desc LIMIT 1"; 

    $sth = $GLOBALS['dbh']->query($sql); 
    $count = 0;
    while ($row = $sth->fetch ())
    { 
        if($column == "source"){ 
            return htmlspecialchars($row['table_name']);
        }
        else{
            return htmlspecialchars($row['epoch_time']);
        }$count++;
    }

}

function getLastHit($source){

    $table_name = '';

    if ($source == 'twitter') {
        $table_name = 'twitter';
    } 
    elseif ($source == 'facebook') {
        $table_name = 'facebook';
    } 
    elseif ($source == 'pastebin') {
        $table_name = 'pastebin';
    } 
    elseif ($source == 'wordpress') {
        $table_name = 'wordpress';
    }
    elseif ($source == 'reddit') {
        $table_name = 'reddit';
    }
    elseif ($source == 'stackexchange') {
        $table_name = 'stackexchange';
    }
    $sql = "SELECT MAX(`epoch_time`) FROM $table_name";

    $sth = $GLOBALS['dbh']->query($sql);  
    $row = $sth->fetch();   

    echo htmlspecialchars(date('M d H:i:s', $row[0]));

}

function getLatestHitLocation(){

    $sql = "SELECT location
            FROM twitter
            WHERE epoch_time = ( 
                SELECT MAX( `epoch_time` ) 
                FROM twitter
                WHERE lat !=  '0.0000000'
                AND lng !=  '0.0000000' )";

    $sth = $GLOBALS['dbh']->query($sql);
    $row = $sth->fetch();

    return htmlspecialchars($row[0]);
}

function getLastChecked($source){

    $sth = $GLOBALS['dbh']->prepare("SELECT `last_checked` FROM `last_checked` WHERE `source` = :source");
    $count = 0;
    $sth->execute(array(':source' => $source));
    while ($row = $sth->fetch ())
    {
        return htmlspecialchars($row[0]);
        $count++;
    }
}

function getTimeSinceLastHit(){

    $difference_in_seconds = time()-htmlspecialchars(getLatestHit("time"));
    $difference_in_hours = floor($difference_in_seconds / 60 / 60);
    $remaining_minutes = floor( (($difference_in_seconds) - ($difference_in_hours * 60 * 60)) / 60);
    return "$difference_in_hours hours, $remaining_minutes minutes";

}

function genSourceBox($source){

    $upperSource = $source;
    $source = strtolower($source);

    echo "<div class='sourceBox'>";
    echo "<h1>$upperSource</h1>";
    echo "<div class='source-logo'><img src='logos/$source.png' alt='$source'></div>";
    echo "<h2>Last checked:</h2><div class='time'>";  echo htmlspecialchars(date('M d H:i:s',getLastChecked($source))); echo "</div><br>";
    echo "<h2>Last hit:</h2><div class='time'>"; print getLastHit($source);   echo "</div>";
    echo "<h3><a href='#' class='$source-select'>Selection</a> ";
    echo "<span class='source-options'><a href='#' class='$source-options'>Options</a></span></h3>";
    echo "</div>";

}

function genSelectedSourceBoxHeader($source, $type){

    $source = strtolower($source);

    echo "<script type='text/javascript' src='js/$source.js'></script>";
    echo "<h1>$type</h1>";
    echo "<div class='source-logo'><img src='logos/$source.png' alt='$source'></div>";
    echo "<h2><a href='#' class='goBack'>Go Back</a></h2>";

}

function genSelectInputUsers(){
    echo '
        <div id="sel-user-keyword">
            <h2>Select User and Keywords</h2>
            <form name="sel-user-keyword" id="sel-user-keyword" action="">
                <fieldset>
                    <label for="user" id="screen-name-label"><h2>User</h2></label>
		    <span id="getUsers"><select id="current-users"><option value=all>all</option></select></span>
                    <label for="keyword" id="keyword-label"><h2>Keyword</h2></label>
                    <span id="getUserKeywords"><select id="user-keywords"><option value=all>all</option></select></span>
                    <input type="submit" name="submit" class="results-button" value="Select" />
		</fieldset>
            </form>
	</div>
    ';
}

function genSelectInput(){
    echo '
        <div id="sel-keyword">
            <h2>Select Keywords</h2>
            <form name="sel-keyword" id="sel-keyword" action="">
	        <fieldset>
		    <label for="keyword" id="keyword-label"><h2>Keyword</h2></label>
		    <span id="getUserKeywords"><select id="keywords"><option value=all>all</option></select></span>
                    <input type="submit" name="submit" class="results-button" value="Select" />
		</fieldset>
	    </form>
	</div>							
    ';
}

function genOptionsInputUsers(){

    echo '<div id="add-user">
              <h2>Add User</h2>
	      <form name="add-user" action="">
	      <fieldset>
	          <label for="user" id="user-label"><h2>User</h2></label>
	          <input type="text" name="user" id="user"></input>
	          <input type="submit" name="submit" class="add-user-button" value="Add User" />
		  <p></p> 
                  <h2>Current users:</h2>
	          <div id="current-users"></div>
                  <input type="submit" name="submit" class="del-user-button" value="Del User" />
	    </fieldset>
	    </form>
	</div>
	<p></p>
	<div id="add-user-keyword">
	    <h2>Add Keywords</h2>
	    <form name="add-user-keyword" id="add-user-keyword" action="">
	        <fieldset>
	            <label for="user" id="screen-name-label"><h2>User</h2></label>
	            <span id=getUsers></span>
	            <label for="keyword" id="keyword-label"><h2>Keyword</h2></label>
	            <input type="text" name="keyword" id="keyword"</input>
	            <input type="submit" name="submit" class="add-keyword-button" value="Add Keyword" />
	            <p></p>
	            <h2>Current keywords for user:</h2>
		    <div id="user-keywords"></div>
                    <input type="submit" name="submit" class="del-keyword-button" value="Del Keyword" />
                </fieldset>
            </form>
        </div>
    ';

}

function genOptionsInput(){
    echo '<div id="add-keyword">
              <h2>Add Keywords</h2>
              <form name="add-keyword" id="add-keyword" action="">
                  <fieldset>
		      <label for="keyword" id="keyword-label"><h2>Keyword</h2></label>
		      <input type="text" name="keyword" id="keyword"</input>
		      <input type="submit" name="submit" class="keyword-button" value="Add Keyword" />
		      <p></p>
		      <h2>Current keywords:</h2>
		      <div id="keywords"></div>
                      <input type="submit" name="submit" class="del-keyword-button" value="Del Keyword" />
	          </fieldset>
              </form>
           </div>
    ';

}

function addUserKeyword($keyword, $source, $user){

    $sql = "INSERT INTO `keywords` (keyword, source, user) VALUES (:keyword, :source, :user)";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->execute(array('keyword' => $keyword, ':source' => $source, ':user' => $user));
    
}

function delUserKeyword($keyword, $source, $user){

    $sql = "DELETE FROM `keywords` WHERE `keyword` = :keyword AND `source` = :source AND `user` = :user";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->execute(array('keyword' => $keyword, ':source' => $source, ':user' => $user));

}

function getUsersKeywords($source, $user, $format){
	
    $sql = "SELECT DISTINCT keyword FROM `keywords` WHERE user = :user AND source = :source ORDER BY keyword";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->bindParam(':user', $user);             
    $sth->bindParam(':source', $source);		                 
    $sth->execute();

    if($format == 'list'){
        echo "<select id='keyword-selection'>";
        echo "<option id='all'>all</option>";
    }
    
    while($row = $sth->fetch()){
	$keyword = htmlspecialchars($row[keyword]);
        if($format == 'list'){
               echo "<option id='$keyword' value='$keyword'>$keyword</option>";
        }
        else{
            echo "'$keyword' ";
        }
    }

}

function genResultsHeader($source_dirty, $results_type_dirty, $user_dirty, $keyword_dirty, $rows_dirty, $page_dirty, $pages_dirty){
    $source = htmlspecialchars($source_dirty);
    $user = htmlspecialchars($user_dirty);
    $keyword = htmlspecialchars($keyword_dirty);
    $rows = htmlspecialchars($rows_dirty);
    $page = htmlspecialchars($page_dirty);
    $pages = htmlspecialchars($pages_dirty);

    echo "<div id='content-header'>";
        echo "<div id='results-source'>$source</div>";
        echo "$results_type from user: <b><span id='results-user'>$user</span></b>, keyword(s): <b><span id='results-keyword'>$keyword</span></b></br>";
        echo "<div class='source-logo'><img src='logos/$source.png' alt='$source'></div>"; 
        echo "Results: $rows.";
        echo "Page ";
        echo '<select id=results-page>';
        for ($i = 1; $i <= $pages; $i++){
            if($i == $page){
                echo "<option selected=$i>$i</option>";
            }
            else{
                echo "<option>$i</option>";
	    }
        }
        echo "</select> of $pages.";
    echo "</div>";
}

}
?>
