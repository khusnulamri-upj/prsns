<?php

include_once "con_db_msaccess.php";

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
