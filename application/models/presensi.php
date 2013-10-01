<?php

class Presensi extends CI_Model {

    var $tbl_presensi = 'mdb_checkinout';

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    function get_all_years() {
        $col_datetime = 'check_time';
        $sql = "SELECT DATE_FORMAT($col_datetime,'%Y') AS year 
            FROM $this->tbl_presensi 
            GROUP BY DATE_FORMAT($col_datetime,'%Y')
            ORDER BY $col_datetime;";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }
    
    function get_all_years_in_array() {
        $array = array();
        if ($this->get_all_years() != false) {
            foreach ($this->get_all_years() as $obj) {
                $array[$obj->year] = $obj->year;
            }
            return $array;
        } else {
            return NULL;
        }
        
    }
    
    function get_by_id($id) {
        $sql = "SELECT user_id, name
            FROM mdb_userinfo
            WHERE user_id = $id
            LIMIT 1";
        $query = $this->db->query($sql);
        if ($query->num_rows() == 1) {
            return $query->result();
        } else {
            return false;
        }
    }
}

?>
