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

function addUser($author){
    
    $sql = "INSERT INTO `reddit_users` (author) VALUES (:author)";
    $sth = $GLOBALS['dbh']->prepare($sql);
    $sth->execute(array(':author' => $author));
    
}   

function delUser($author){

    $reddit_sql = "DELETE FROM `reddit_users` WHERE author = :author";
    $sth = $GLOBALS['dbh']->prepare($reddit_sql);
    $sth->execute(array(':author' => $author));

    $keyword_sql = "DELETE FROM `keywords` WHERE user = :user AND source = 'reddit'";
    $sth = $GLOBALS['dbh']->prepare($keyword_sql);
    $sth->execute(array(':user' => $author));

}

function getUsers($format){
        
    $sql = "SELECT author FROM `reddit_users` ORDER BY author asc";
    $sth = $GLOBALS['dbh']->query($sql);
        
    if($format == 'list'){
        echo "<select id='user-selection'>";
        echo "<option id='all'>all</option>";
    }

    while($row = $sth->fetch()){
	$author = htmlspecialchars($row[author]);
        if($format == 'list'){
            echo "<option id='$author' value='$author'>$author</option>";
        }
        else{
            echo "'$author' ";
        }
    }
   
    if($format == 'list'){
        echo "</select>";
    }
}

function getResults($page, $author, $keyword){

    $sql = "SELECT epoch_time, author, body, permalink FROM reddit ";
    $where = "WHERE 1 = 1 "; 

    if ($author != 'all'){
        $where .= "AND author = :author ";
    }
    if($keyword != 'all'){
        $where .= "AND MATCH(body) AGAINST (:keyword IN BOOLEAN MODE) ";
    }

    $sql .= $where;
    $sql .= "ORDER BY epoch_time DESC ";
    $sth = $GLOBALS['dbh']->prepare($sql);

    if($author != 'all'){
        $sth->bindParam(':author', $author);
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
    if($author != 'all'){
        $sth->bindParam(':author', $author);
    }
    if($keyword != 'all'){
        $sth->bindParam(':keyword', $keyword);
    }
    $sth->execute();

    genResultsHeader('reddit', 'Posts', $author, $keyword, $rows, $page, $pages);
    if($rows > 0){
        echo('<div class="reddit-content">
              <table>
              <tbody>
                <tr>
                    <th>TimeStamp</th>
                    <th>Author</th>
                    <th>Body</th>
                    <th>Matched Keyword</th>
                </tr>');
        while ($row = $sth->fetch ()){
            $epoch_time = htmlspecialchars($row['epoch_time']);
            $real_time  = htmlspecialchars(date('M d H:i:s',$epoch_time));
            $author     = htmlspecialchars($row['author']);
            $author_url = "<a href=http://reddit.com/user/$author>$author</a>";
            $body       = htmlspecialchars(substr($row['body'], 0, 150));
            $permalink  = htmlspecialchars($row['permalink']);
            $keyword    = htmlspecialchars($keyword);
            $body .= " <a href=$permalink target='_blank'>[...]</a>";
	    
	    printf ("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", $real_time, $author_url, $body, $keyword);
        }
        echo '</tbody></table></div>';
    }
    else{
        echo "No results!";
    }
}

}

?>
