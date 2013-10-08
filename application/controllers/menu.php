<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Menu extends CI_Controller {

    public function index() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $this->load->view('menu_lst');
        }
    }

}