<?php

try {
    $dbh = new PDO("odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq=D:\data\att2000.mdb;");
} catch (PDOException $e) {
    echo $e->getMessage();
}

$sql = "SELECT * FROM CHECKINOUT WHERE ";
    foreach ($dbh->query($sql) as $row)
        {
        print $row['USERID'] . '<br />';
        }

    /*** close the database connection ***/
    $dbh = null;

?>