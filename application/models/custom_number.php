<?php

class Custom_number extends CI_Model {

    var $months_indonesia = array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function format_string_front_zero($value) {
        $limit = 10;
        if ($value < $limit) {
            return '0'.$value;
        }
        return $value;
    }
    
}

?>
