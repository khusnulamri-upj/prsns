<?php

class Personil extends CI_Model {

    //var $date    = '';

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    function get_all() {
        $sql = "SELECT user_id, name 
            FROM mdb_userinfo 
            ORDER BY name";
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
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
