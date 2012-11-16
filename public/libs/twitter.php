<?php

if(!isset($_SESSION)){
    session_start();
}

if (!isset($_SESSION['user'])){

    header('Location: index.php');

}
else{

require_once($_SERVER['DOCUMENT_ROOT'].'/config/db.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/libs/functions.php');

function addUser($user){

    $sql = "INSERT INTO `twitter_users` (user) VALUES (:user)";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->execute(array(':user' => $user));

}

function delUser($user){

    $twitter_sql = "DELETE FROM `twitter_users` WHERE user = :user";
    $sth = $GLOBALS['dbh']->prepare($twitter_sql);
    $sth->execute(array(':user' => $user));
    
    $keyword_sql = "DELETE FROM `keywords` WHERE user = :user AND source = 'twitter'";
    $sth = $GLOBALS['dbh']->prepare($keyword_sql);
    $sth->execute(array(':user' => $user));

}

function getUsers($format){

    $sql = "SELECT user FROM `twitter_users` ORDER BY user asc";
    $sth = $GLOBALS['dbh']->query($sql);
    
    if($format == 'list'){
        echo "<select id='user-selection'>";
        echo "<option id='all'>all</option>";
    }
    
    while($row = $sth->fetch()){
	$user = htmlspecialchars($row[user]);
        if($format == 'list'){
            echo "<option id='$user' value='$user'>$user</option>";
        }
	else{
            echo "'$user' ";
        }
    }

    if($format == 'list'){
        echo "</select>";
    }    
}

function getResults($page, $user, $keyword){

    $sql = "SELECT epoch_time, from_user, text, twitter_id, keyword, profile_image_url_https FROM twitter ";
    $where = "WHERE 1 = 1 ";

    if ($user != 'all'){
        $where .= "AND from_user = :user ";
    }
    if($keyword != 'all'){
        $where .= "AND MATCH(text) AGAINST (:keyword IN BOOLEAN MODE) ";
    }
    
    $sql .= $where;
    $sql .= "ORDER BY epoch_time DESC ";
    $sth = $GLOBALS['dbh']->prepare($sql);
    
    if($user != 'all'){
        $sth->bindParam(':user', $user);
    }
    if($keyword != 'all'){
        $sth->bindParam(':keyword', $keyword);
    }
    $sth->execute();

    $rows = $sth->rowCount();
    $pages = ceil($rows/10);    
    $start = ($page-1)*10;

    $sql .= "LIMIT :start, 10";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->bindParam(':start', $start, PDO::PARAM_INT);
    if($user != 'all'){
        $sth->bindParam(':user', $user);
    }
    if($keyword != 'all'){
        $sth->bindParam(':keyword', $keyword);
    }
    $sth->execute();

    genResultsHeader('twitter', 'Tweets', $user, $keyword, $rows, $page, $pages);
    if($rows > 0){
        echo('<div class="twitter-content">
              <table>
              <tbody>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Text</th>
                    <th>Matched Keyword<th>
                </tr>');
        while($row = $sth->fetch()){
            $epoch_time = htmlspecialchars($row['epoch_time']);
            $real_time  = htmlspecialchars(date('M d H:i:s',$epoch_time));
            $from_user  = htmlspecialchars($row['from_user']);
            $text       = htmlspecialchars($row['text']);
            $twitter_id = htmlspecialchars($row['twitter_id']);
            $keyword    = htmlspecialchars($keyword);
            $image      = htmlspecialchars(stripslashes($row['profile_image_url_https']));
            $created_at = htmlspecialchars(date('Y-M-d H:i:s',(strtotime($epoch_time))));
            $text .= " <a href='https://twitter.com/$from_user/status/$twitter_id' target='_blank'>[...]</a>";
	    
	    printf ("<tr><td>%s</td><td><div class=twitter-profile-pic><img src='%s'/></div><a href='https://twitter.com/%s' target='_blank'>%s</a></td><td>%s</td><td>%s</td></tr>\n", $real_time, $image, $from_user, $from_user, $text,  $keyword);
        }
        echo '</tbody></table></div>';
    }
    else{
        echo "No results!";
    }
}

}

?>
