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

function getResults($page, $keyword){

    $sql = "SELECT epoch_time, profile_picture, user_id, name, message, keyword FROM facebook ";
    $where = "WHERE 1 = 1 ";

    if($keyword != 'all'){
        $where .= "AND MATCH(message) AGAINST (:keyword IN BOOLEAN MODE) ";
    }

    $sql .= $where;
    $sql .= "ORDER BY epoch_time DESC ";

    $sth = $GLOBALS['dbh']->prepare($sql);
    if($keyword != 'all'){
        $sth->bindParam(':keyword', $keyword);
    }
    $sth->execute();
    
    $rows = $sth->rowCount();
    $pages = ceil($rows/10);    
    $start = ($page-1)*10;
 
    $sql .= " LIMIT :start, 10";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->bindParam(':start', $start, PDO::PARAM_INT);
    if($keyword != 'all'){
        $sth->bindParam(':keyword', $keyword);
    }   
    $sth->execute(); 

    genResultsHeader('facebook', 'Posts', 'all', $keyword, $rows, $page, $pages);
    if($rows > 0){
        echo '<div class="facebook-content">
            <table>
                <tbody>
                    <tr>
                        <th>Time</th>
                        <th>Profile Picture</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Keyword</th>
                    </tr>';

        while ($row = $sth->fetch ())
        {
	    $epoch_time      = htmlspecialchars($row['epoch_time']);
	    $profile_pic_url = htmlspecialchars($row[profile_picture]);
            $profile_picture = "<img src=$profile_pic_url/>";
            $time            = (date('M d h:i:s',$epoch_time));
            $user_id         = htmlspecialchars($row['user_id']);
            $name            = htmlspecialchars($row['name']);
            $status          = htmlspecialchars(substr($row['message'], 0, 150));
            $status         .= " <a href='http://facebook.com/$user_id' target='_blank'>[...]</a>";
            $keyword        = htmlspecialchars($keyword);

            printf ("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", $time, $profile_picture, $name, $status, $keyword);
            $count++;
        }
        echo '</tbody></table></div>';
    }
    else{
        echo "No results.";
    }
 
}

}

?>
