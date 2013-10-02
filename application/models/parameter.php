<?php

class Parameter extends CI_Model {

    //var $date    = '';

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database();
    }

    function get_value($name, $type = 'VARIABLE') {
        $id = strtoupper($name);
        $sql = "SELECT value
            FROM parameter
            WHERE type = '$type'
                AND name = '$id'
            LIMIT 1";
        $query = $this->db->query($sql);
        $row = $query->row();
        if (isset($row)) {
            return $row->value;
        } else {
            return NULL;
        }
    }

}

?>
