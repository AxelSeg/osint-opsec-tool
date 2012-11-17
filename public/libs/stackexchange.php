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

function addUser($account_id){

    $sql = "INSERT INTO `stackexchange_users` (account_id) VALUES (:account_id)";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->execute(array(':account_id' => $account_id));

}

function delUser($account_id){

    $stackexchange_sql = "DELETE FROM `stackexchange_users` WHERE account_id = :account_id";
    $sth = $GLOBALS['dbh']->prepare($stackexchange_sql);
    $sth->execute(array(':account_id' => $account_id));

    $keyword_sql = "DELETE FROM `keywords` WHERE user = :account_id AND source = 'stackexchange'";
    $sth = $GLOBALS['dbh']->prepare($keyword_sql);
    $sth->execute(array(':account_id' => $account_id));

}

function getUsers($format){

    $sql = "SELECT display_name, account_id FROM `stackexchange_users` ORDER BY account_id asc";
    $sth = $GLOBALS['dbh']->query($sql);
    
    if($format == 'list'){
        echo "<select id='user-selection'>";
        echo "<option id='all'>all</option>";
    }
    
    while($row = $sth->fetch()){
	$account_id = htmlspecialchars($row[account_id]);
        $display_name = htmlspecialchars($row[display_name]);
        if($format == 'list'){
            echo "<option id='$account_id' value='$account_id'>$display_name</option>";
        }
	else{
            echo "'$account_id' ";
        }
    }

    if($format == 'list'){
        echo "</select>";
    }
}

function getResults($page, $user, $keyword){

    $sql = "SELECT epoch_time, profile_image, account_id, site, content_type, content, url, display_name FROM stackexchange ";
    $where = "WHERE 1 = 1 ";

    if ($user != 'all'){
        $where .= "AND account_id = :user ";
    }
    if($keyword != 'all'){
        $where .= "AND MATCH(content) AGAINST (:keyword IN BOOLEAN MODE) ";
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

    genResultsHeader('stackexchange', 'Posts', $user, $keyword, $rows, $page, $pages);
    if($rows > 0){
        echo('<div class="stackexchange-content">
              <table>
              <tbody>
                <tr>
                    <th>Time</th>
                    <th>Account</th>
                    <th>Site</th>
		    <th>Content Type</th>
                    <th>Content</th>
                    <th>Keyword</th>
                </tr>');
            while($row = $sth->fetch()){
                $epoch_time   = htmlspecialchars($row['epoch_time']);
                $real_time    = htmlspecialchars(date('M d H:i:s',$epoch_time));
                $profile_image_url  = htmlspecialchars($row['profile_image']);
		$profile_image = "<div class=twitter-profile-pic><img src=$profile_image_url width=50px height=50px /></div>";
		$display_name = htmlspecialchars($row['display_name']);
		$account_id   = htmlspecialchars($row['account_id']);
		$account_id_url = "<a href=http://stackexchange.com/users/$account_id target='blank'>$display_name</a>";
                $site         = htmlspecialchars($row['site']);
                $content_type = htmlspecialchars($row['content_type']);
                $content      = htmlspecialchars(substr($row['content'], 0, 150));
                $created_at   = htmlspecialchars(date('Y-M-d H:i:s',(strtotime($epoch_time))));
                $url          = htmlspecialchars($row['url']);
		$content     .= " <a href='$url' target='_blank'>[...]</a>";
                $keyword  = htmlspecialchars($keyword);
                printf ("<tr><td>%s</td><td>%s%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", $real_time, $profile_image, $account_id_url, $site, $content_type, $content, $keyword_clean);

        }
        echo '</tbody></table></div>';
    }
    else{
        echo "No results!";
    }
}

}

?>
