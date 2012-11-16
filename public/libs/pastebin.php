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

    $sql = "SELECT epoch_time, pasteID, paste, keyword FROM pastebin ";
    $where = "WHERE 1 = 1 ";

    if($keyword != 'all'){
        $where .= "AND MATCH(paste) AGAINST (:keyword IN BOOLEAN MODE) ";
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

    genResultsHeader('pastebin', 'Pastes', 'all', $keyword, $rows, $page, $pages);
    if($rows > 0){    
        echo '<div class="pastebin-content">
            <table>
                <tbody>
                    <tr>
                        <th>Time</th>
                        <th>Paste</th>
                        <th>Keyword</th>';
        while ($row = $sth->fetch ())
	{
            $clean_epoch = htmlspecialchars($row[epoch_time]);
	    $clean_paste = htmlspecialchars($row[paste]);
            $timeStamp = htmlspecialchars(date('M d h:i:s', $clean_epoch));
            $paste     = htmlspecialchars(substr($clean_paste, 0, 150));
            $pasteID   = htmlspecialchars($row[pasteID]);
            $keyword   = htmlspecialchars($keyword);
            $paste    .= " <a href='http://pastebin.com/$pasteID' target='_blank'>[...]</a>";
            printf ("<tr><td>%s</td><td>%s</td><td>%s</td></tr>\n", $timeStamp, $paste, $keyword);
            $count++;
        }
     echo'</tbody></table></div>';
    }
    else{
        echo "No results.";
    }
}

}
?>
