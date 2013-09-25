<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$a[0] = array(
    "one" => 1,
    "two" => 2,
    "three" => 3,
    "seventeen" => 17
);

$a[1] = array(
    "one" => 9,
    "two" => 9,
    "three" => 9,
    "seventeen" => 9
);

foreach ($a as $b) {
    foreach ($b as $k => $v) {
        echo "\$a[$k] => $v.\n";
    }
    echo " - ";
}

?>
