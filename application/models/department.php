<?php

class Department extends CI_Model {

    //var $date    = '';

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    function get_all() {
        $sql = "SELECT dept_id, dept_name 
            FROM mdb_departments 
            ORDER BY dept_name";
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
                $array[$obj->dept_id] = strtoupper($obj->dept_name);
            }
            return $array;
        } else {
            return NULL;
        }
        
    }
    
    function get_by_id($id) {
        $sql = "SELECT dept_id, dept_name 
            FROM mdb_departments
            WHERE dept_id = $id
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
