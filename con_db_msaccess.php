<?php

//$dbMsAccessName = $_SERVER["DOCUMENT_ROOT"] . "att2000.mdb";
$dbMsAccessName = "D:\UPJ\Attendance\att2000.mdb";

if (!file_exists($dbMsAccessName)) {
    die("Could not find database file.");
}

echo "CREATE PDO";

$dbMsAccess = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbMsAccessName; Uid=; Pwd=;");

$sql = "SELECT * FROM CHECKINOUT";
//$sql .= " FROM product p, product_category pc";
//$sql .= " WHERE p.id = pc.productId";
//$sql .= " AND pc.category_id = " . $categoryId;
//$sql .= " ORDER BY name";

echo $sql;

$result = $dbMsAccess->query($sql);

print_r($result);

while ($row = $result->fetch()) {
    $USERID = $row["USERID"];
    $CHECKTIME = $row["CHECKTIME"];
    echo $USERID, ' - ', $CHECKTIME;
}

?>
