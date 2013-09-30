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
        } else if ($user_id == 'ALL') {
            redirect(site_url("att_rpt/dtl_prsn_xls/".$month."_".$year."_ALL"), 'location');
        }

        //$jam_telat_masuk = '07:40:00';
        //$jam_tengah = '12:00:00';
        
        $jam_telat_masuk = '07:40';
        $jam_tengah = '12:00';
        
        //$time_format = "%T";
        $time_format = "%H:%i";

        $data['filter_libur'] = array('Sat', 'Sun');
        $data['filter_mmyyyy'] = (($month < 10) ? "0" . $month : $month) . "/" . $year;
        
        $sql = "SELECT content, opt_keterangan_id AS id
            FROM opt_keterangan
            ORDER BY content";

        $query = $this->db->query($sql);

        $opt_ket = $query->result();

        $sql = "SELECT u.name, d.dept_name
            FROM mdb_userinfo u
            LEFT OUTER JOIN mdb_departments d ON u.default_dept_id = d.dept_id
            WHERE u.user_id = $user_id
            LIMIT 1";

        $query = $this->db->query($sql);

        $row = $query->row();

        /* $sql = "SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
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
          ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')"; */

        $sql = "SELECT B.*
            FROM (
            SELECT A.*
            FROM (
            SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')) AS jam_masuk,
            IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'$time_format')) > 0,'',DATE_FORMAT(MAX(io.check_time),'$time_format')) AS jam_keluar,
            IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_telat_masuk')) AS waktu_telat,
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') > '$jam_telat_masuk', 1, 0) AS is_late,
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') = DATE_FORMAT(MAX(io.check_time),'$time_format'), 1, 0) AS is_same,
            TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_telat_masuk'))) AS sec_waktu_telat,
            NULL AS ket
            FROM mdb_checkinout io
            WHERE io.user_id = $user_id
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $data['filter_mmyyyy'] . "'
            GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ) A
            UNION
            SELECT k.user_id AS user_id,
            DATE_FORMAT(k.tgl,'%d/%m/%Y') AS tgl_presensi,
            NULL AS jam_masuk,
            NULL AS jam_keluar,
            NULL AS waktu_telat,
            NULL AS is_late,
            NULL AS is_same,
            NULL AS sec_waktu_telat,
            k.opt_keterangan AS ket
            FROM keterangan k
            WHERE k.user_id = $user_id
            AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $data['filter_mmyyyy'] . "'
            AND k.expired_time IS NULL
            GROUP BY DATE_FORMAT(k.tgl,'%d/%m/%Y')
            ) B
            ORDER BY B.tgl_presensi";

        $query = $this->db->query($sql);

        $data['att_user_id'] = $user_id;
        $data['att_opt_ket'] = $opt_ket;
        $data['att_kode'] = "attrpt0A [" . $month . "_" . $year . "_" . $user_id . "]";
        $data['att_filter'] = $month . "_" . $year . "_" . $user_id;
        $data['att_mnth'] = $month;
        //$data['att_mnth_name'] = $month;
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

    public function dtl_prsn_sv_ket() {
        $this->load->database();

        $ket = $this->input->post('ket');
        $month = ($this->input->post('month') < 10) ? "0" . $this->input->post('month') : $this->input->post('month');
        $year = $this->input->post('year');
        $user_id = $this->input->post('user_id');
        //print_r($this->input->post('ket'));
        //$this->db->trans_start();
        
        foreach ($ket as $key => $value) {
            $tanggal = $year . "-" . $month . "-" . $key;

            if ((isset($value)) && ($value > 0)) {
                $key_f = ($key < 10) ? "0" . $key : $key;

                $str = "UPDATE keterangan SET expired_time = CURRENT_TIMESTAMP WHERE expired_time IS NULL AND user_id = $user_id AND DATE_FORMAT(tgl,'%d/%m/%Y') LIKE '$key_f/$month/$year'";

                $query = $this->db->query($str);

                $data_mysql = array(
                    'user_id' => $user_id,
                    'tgl' => $tanggal,
                    'opt_keterangan' => $value
                );

                $this->db->insert('keterangan', $data_mysql);
            }
        }

        //$this->db->trans_complete();
        $data['month'] = $this->input->post('month');
        $data['year'] = $this->input->post('year');
        $data['user_id'] = $this->input->post('user_id');
        $this->load->view('att_dtl_prsn_sv_ket', $data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */