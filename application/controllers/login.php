<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Login extends CI_Controller {

    function index() {
        if ($this->session->userdata('username') == '') {
            $data['css_content'] = 'files/css/login.css';
            $data['main_content'] = 'lgn_frm';
            $this->load->view('includes/template_cstm',$data);
        } else {
            redirect('menu');
        }
        
    }
    
    function validate_credentials() {
        $this->load->model('membership');
        $query = $this->membership->validate($this->input->post('username'),
                $this->input->post('password'));
        
        if ($query) {
            $data = array('username' => $this->input->post('username'),
                'is_logged_in' => true,
                'credentials' => $this->membership->getMemberId($this->input->post('username'),$this->input->post('password')));
            
            $this->session->set_userdata($data);
            redirect('menu');
        } else {
            $data['css_content'] = 'files/css/login.css';
            $data['main_content'] = 'lgn_frm';
            $data['mssg_error'] = 'Username/Password Salah';
            $this->load->view('includes/template_cstm',$data);
        }
    }
    
    function signup() {
        /*$data['css_content'] = 'files/css/login.css';
        $data['main_content'] = 'lgn_sgnp_frm';
        $this->load->view('includes/template_cstm',$data);*/
    }
    
    function create_member() {
        /*$this->load->library('form_validation');
        
        $this->form_validation->set_rules('email_address','Email','trim|required|valid_email');
        $this->form_validation->set_rules('username','Username','trim|required|min_length[4]');
        $this->form_validation->set_rules('password','Password','trim|required|min_length[4]|max_length[32]');
        $this->form_validation->set_rules('passwordcon','Password Confirm','trim|required|matches[password]');
        
        if ($this->form_validation->run() == FALSE ) {
            $data['css_content'] = 'files/css/login.css';
            $data['main_content'] = 'lgn_sgnp_frm';
            $this->load->view('includes/template_cstm', $data);
        } else {
            $this->load->model('membership');
            if ($query = $this->membership->create_member($this->input->post('username'),
                    $this->input->post('password'),
                    $this->input->post('email_address'))) {
                $data['css_content'] = 'files/css/login.css';
                $data['main_content'] = 'lgn_sgnp_sccss';
                $this->load->view('includes/template_cstm', $data);
            } else {
                $data['css_content'] = 'files/css/login.css';
                $data['main_content'] = 'lgn_sgnp_frm';
                $this->load->view('includes/template_cstm', $data);
            }
        }*/
    }
    
    function logout() {  
        $this->session->sess_destroy();  
        redirect('login');  
    }  
    
}