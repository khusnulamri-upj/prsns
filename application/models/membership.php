<?php

class Membership extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->load->database('default');
    }

    function validate($username,$password) {
        $this->db->where('username',$username);
        $this->db->where('password',md5($password));
        $query = $this->db->get('membership');
        
        if ($query->num_rows == 1) {
            return true;
        }
        return false;
    }
    
    function create_member($username,$password,$email_address) {
        $new_member = array('username' => $username,
            'password' => md5($password),
            'email_address' => $email_address
            );
        
        $insert = $this->db->insert('membership',$new_member);
        return $insert;
    }
    
    function getMemberId($username,$password) {
        $this->db->where('username',$username);
        $this->db->where('password',md5($password));
        $query = $this->db->get('membership');
        
        if ($query->num_rows == 1) {
            $row = $query->row();
            return $row->membership_id;
        }
        return false;
    }
    
}

?>
