<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Att_rpt extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -  
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in 
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index() {
        $this->load->view('att_rpt_lst');
    }

    public function lst() {
        $this->load->view('att_rpt_lst');
    }

    public function dtl_prsn() {
        $this->load->database();
        $sql = "SELECT user_id, name 
            FROM mdb_userinfo 
            ORDER BY name";
        $query = $this->db->query($sql);
        $data['prsn'] = $query->result();
        $sql = "SELECT DATE_FORMAT(check_time,'%Y') AS year 
            FROM mdb_checkinout 
            GROUP BY DATE_FORMAT(check_time,'%Y')
            ORDER BY check_time;";
        $query = $this->db->query($sql);
        $data['years'] = $query->result();
        $this->load->view('att_dtl_prsn_fltr', $data);
    }

    public function dtl_prsn_vw() {
        $this->load->database();

        $month = $this->input->post('month');
        $year = $this->input->post('year');
        $user_id = $this->input->post('user_id');
        
        if (empty($user_id)) {
            redirect('att_rpt/lst', 'location');
        }
        
        $jam_telat_masuk = '07:40:00';
        $jam_tengah = '12:00:00';

        $data['filter_libur'] = array('Sat', 'Sun');
        $data['filter_mmyyyy'] = (($month < 10) ? "0" . $month : $month) . "/" . $year;
        
        $sql = "SELECT u.name, d.dept_name
            FROM mdb_userinfo u
            LEFT OUTER JOIN mdb_departments d ON u.default_dept_id = d.dept_id
            WHERE u.user_id = $user_id
            LIMIT 1";
        
        $query = $this->db->query($sql);
        
        $row =  $query->row();
        
        /*$sql = "SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')) AS jam_masuk,
            IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'%T')) > 0,'',DATE_FORMAT(MAX(io.check_time),'%T')) AS jam_keluar,
            IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk')) AS waktu_telat,
            IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_telat_masuk', 1, 0) AS is_late,
            IF(MIN(io.check_time) = MAX(io.check_time), 1, 0) AS is_same,
            TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk'))) AS sec_waktu_telat
            FROM mdb_checkinout io
            WHERE io.user_id = $user_id
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $data['filter_mmyyyy'] . "'
            GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')";*/
        
        $sql = "SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%H:%i')) AS jam_masuk,
            IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'%T')) > 0,'',DATE_FORMAT(MAX(io.check_time),'%H:%i')) AS jam_keluar,
            IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk')) AS waktu_telat,
            IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_telat_masuk', 1, 0) AS is_late,
            TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk'))) AS sec_waktu_telat
            FROM mdb_checkinout io
            WHERE io.user_id = $user_id
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $data['filter_mmyyyy'] . "'
            GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')";
        
        echo $sql;
        
        $query = $this->db->query($sql);
        
        $data['att_kode'] = "attrpt0A [".$month."_".$year."_".$user_id."]";
        $data['att_filter'] = $month."_".$year."_".$user_id;
        $data['att_mnth'] = $month;
        $data['att_year'] = $year;
        $data['att_nama'] = $row->name;
        $data['att_dept'] = $row->dept_name;
        $data['att_loop'] = days_in_month($month, $year);
        $data['att_prsn'] = $query->result();
        
        if ($query->num_rows > 0) {
            $this->load->view('att_dtl_prsn_vw', $data);
        } else {
            $data['mssg'] = "Data tidak ditemukan.";
            $this->load->view('mssg', $data);
        }
    }
    
    public function dtl_prsn_xls() {
        $filter = $this->uri->segment(3);
        redirect(base_url("/thirdparty/att_dtl_prsn_xls.php?fltr=$filter"), 'location');
    }
    
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */