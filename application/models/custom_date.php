<?php

class Custom_date extends CI_Model {

    var $months_indonesia = array('Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    function set_timezone($value = 'Asia/Jakarta') {
        date_default_timezone_set($value);
    }
    
    function get_timezone() {
        return date_default_timezone_get();
    }

    function get_all_months($country) {
        $array = array();
        for ($i = 1; $i <= 12; $i++) {
            if (strtoupper($country) == 'INDONESIA') {
                $array[$i] = $this->get_indonesia_month($i);
            } else {
                $array[$i] = date("F", mktime(0, 0, 0, $i + 1, 0, 0));
            }
        }
        return $array;
    }
    
    function get_all_in_array() {
        $array = array();
        if ($this->get_all() != false) {
            foreach ($this->get_all() as $obj) {
                $array[$obj->user_id] = strtoupper($obj->name);
            }
            return $array;
        } else {
            return NULL;
        }   
    }
    
    function get_indonesia_month($id) {
        if (($id >= 0) && ($id <= 12)) {
            return $this->months_indonesia[$id-1];
        } else {
            return NULL;
        }
    }
}

?>
