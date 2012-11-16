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

    $sql = "SELECT epoch_time, title, author, content, keyword, link FROM wordpress ";
    $where = "WHERE 1 = 1 ";

    if($keyword != 'all'){
        $where .= "AND MATCH(content) AGAINST (:keyword IN BOOLEAN MODE) ";
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

    genResultsHeader('wordpress', 'Blogs', 'all', $keyword, $rows, $page, $pages);
    if($rows > 0){
        echo('<div class="twitter-content">
              <table>
              <tbody>
                <tr>
                    <th>Time</th>
                    <th>Author</th>
		    <th>Title</th>
                    <th>Content</th>
                    <th>Matched Keyword<th>
                </tr>');
            while($row = $sth->fetch()){
                $epoch_time = htmlspecialchars($row[epoch_time]);
                $real_time  = htmlspecialchars(date('M d H:i:s',$epoch_time));
		$title      = htmlspecialchars(substr($row[title], 0, 30));
		$title     .= "...";
                $author     = htmlspecialchars($row[author]);
                $content    = htmlspecialchars(substr($row[content], 0, 150));
                $keyword    = htmlspecialchars($keyword);
                $link       = htmlspecialchars(stripslashes($row[link]));
                $content   .= " <a href='$link' target='_blank'>[...]</a>";

                printf ("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", $real_time, $author, $title, $content,  $keyword);

        }
        echo '</tbody></table></div>';
    }
    else{
        echo "No results!";
    }
}

}

?>
