<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Report extends CI_Controller {

    public function index() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $this->load->view('rpt_lst');
        }
    }
        
    public function filter_personal_monthly() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $this->load->model('Personil');
            $this->load->model('Presensi');
            $data['prsnl'] = $this->Personil->get_all_in_array();
            $data['bln'] = $this->Custom_date->get_all_months('indonesia');
            $data['thn'] = $this->Presensi->get_all_years_in_array();
            $this->load->view('rpt_fltr', $data);
        }
    }
    
    public function detail_personal_monthly() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $bln = $this->input->post('bulan');
            $thn = $this->input->post('tahun');
            $user_id = $this->input->post('id');

            if (empty($user_id)) {
                redirect('report/filter_personal_monthly', 'location');
            } else if ($user_id == 'ALL') {
                if (isset($bln) && isset($thn)) {
                    //redirect(site_url("att_rpt/dtl_prsn_xls/".$bln."_".$thn."_ALL"), 'location');
                    //redirect(base_url("/thirdparty/detail_personal_monthly_xls.php?fltr=".$bln."_".$thn."_ALL"), 'location');
                    redirect(site_url("report/detail_personal_monthly_all/$bln/$thn"),'location');
                }
                //echo $bln.' '.$thn.' '.$user_id;
                redirect('report/filter_personal_monthly', 'location');
            }

            $jam_masuk = $this->Parameter->get_value('jam_masuk');
            $jam_keluar = $this->Parameter->get_value('jam_keluar');
            $jam_tengah = $this->Parameter->get_value('jam_tengah');

            $time_format = "%H:%i";

            $data['filter_libur'] = array('Sat', 'Sun');
            $data['filter_mmyyyy'] = $this->Custom_number->format_string_front_zero($bln) . "/" . $thn;

            $sql = "SELECT content, opt_keterangan_id AS id
            FROM opt_keterangan
            ORDER BY order_no";

            $query = $this->db->query($sql);

            $opt_ket = $query->result();

            $sql = "SELECT u.name, d.dept_name
            FROM mdb_userinfo u
            LEFT OUTER JOIN mdb_departments d ON u.default_dept_id = d.dept_id
            WHERE u.user_id = $user_id
            LIMIT 1";

            $query = $this->db->query($sql);

            $row = $query->row();

            $sql = "SELECT B.*, MAX(B.ket) AS ket2, IF((B.ket = 2) OR (B.ket = 1), 0, 1) AS counter
            FROM (
            SELECT A.*
            FROM (
            SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')) AS jam_masuk,
            IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'$time_format')) > 0,'',DATE_FORMAT(MAX(io.check_time),'$time_format')) AS jam_keluar,
            IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk')) AS waktu_telat,
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') > '$jam_masuk', 1, 0) AS is_late,
            IF(DATE_FORMAT(MAX(io.check_time),'%H:%i') < '$jam_keluar', 1, 0) AS is_late2, 
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') = DATE_FORMAT(MAX(io.check_time),'$time_format'), 1, 0) AS is_same,
            TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk'))) AS sec_waktu_telat,
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
            NULL AS is_late2,
            NULL AS is_same,
            NULL AS sec_waktu_telat,
            k.opt_keterangan AS ket
            FROM keterangan k
            WHERE k.user_id = $user_id
            AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $data['filter_mmyyyy'] . "'
            AND k.expired_time IS NULL
            GROUP BY DATE_FORMAT(k.tgl,'%d/%m/%Y')
            ) B
            GROUP BY B.tgl_presensi
            ORDER BY B.tgl_presensi";

            $query = $this->db->query($sql);

            //echo $sql;

            $data['att_user_id'] = $user_id;
            $data['att_opt_ket'] = $opt_ket;
            $data['att_kode'] = "attrpt0A [" . $bln . "_" . $thn . "_" . $user_id . "]";
            $data['att_filter'] = $bln . "_" . $thn . "_" . $user_id;
            $data['att_mnth'] = $bln;
            //$data['att_mnth_name'] = $bln;
            $data['att_mnth_name'] = $this->Custom_date->get_indonesia_month($bln);
            $data['att_year'] = $thn;
            $data['att_nama'] = $row->name;
            $data['att_dept'] = $row->dept_name;
            $data['att_loop'] = days_in_month($bln, $thn);
            $data['att_prsn'] = $query->result();

            //if ($query->num_rows > 0) {
            if ($query->num_rows() > 0) {

                $sql = "SELECT o.opt_keterangan_id AS id, o.content AS keterangan, count(a.user_id) AS jumlah
                FROM opt_keterangan o
                LEFT OUTER JOIN (
                SELECT k.*
                FROM keterangan k
                WHERE k.user_id = $user_id
                AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $data['filter_mmyyyy'] . "'
                AND k.expired_time IS NULL
                ) a ON o.opt_keterangan_id = a.opt_keterangan
                GROUP BY o.opt_keterangan_id";


                /* $sql = "SELECT o.opt_keterangan_id AS id, o.content AS keterangan, count(k.user_id) AS jumlah
                  FROM keterangan k
                  LEFT OUTER JOIN opt_keterangan o ON o.opt_keterangan_id = k.opt_keterangan
                  WHERE k.user_id = $user_id
                  AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $data['filter_mmyyyy'] . "'
                  AND k.expired_time IS NULL
                  GROUP BY o.opt_keterangan_id"; */

                $query = $this->db->query($sql);

                $data['att_resume'] = $query->result();

                //echo $query->num_rows;

                $this->load->view('rpt_dtl_prsn_mnthly', $data);
            } else {
                $data['mssg'] = "Data tidak ditemukan.";

                //echo $query->num_rows;

                $this->load->view('mssg', $data);
            }
        }
    }
    
    public function detail_personal_monthly_xls() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $filter = $this->uri->segment(3);
            redirect(base_url("/thirdparty/detail_personal_monthly_xls.php?fltr=$filter"), 'location');
        }
    }
    
    public function filter_department_yearly() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $this->load->model('Department');
            $this->load->model('Presensi');
            $data['dept'] = $this->Department->get_all_in_array();
            $data['thn'] = $this->Presensi->get_all_years_in_array();
            $this->load->view('rpt_fltr_dept', $data);
        }
    }
    
    public function summary_department_yearly_xls() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $thn = $this->input->post('tahun');
            $dept_id = $this->input->post('id');
            $filter = $thn . '_' . $dept_id;
            
            if (empty($dept_id)) {
                redirect('report/filter_department_yearly', 'location');
            } else if ($dept_id == 'ALL') {
                if (isset($thn)) {
                    //redirect(site_url("att_rpt/dtl_prsn_xls/".$bln."_".$thn."_ALL"), 'location');
                    //redirect(base_url("/thirdparty/detail_personal_monthly_xls.php?fltr=".$bln."_".$thn."_ALL"), 'location');
                    redirect(site_url("report/summary_department_yearly_all/$thn"),'location');
                }
                //echo $bln.' '.$thn.' '.$user_id;
                redirect('report/filter_department_yearly', 'location');
            }
            
            redirect(base_url("/thirdparty/summary_department_yearly_xls.php?fltr=$filter"), 'location');
        }
    }
    
    
    
    public function dtl_prsn_vw() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $this->load->database();

            $month = $this->input->post('month');
            $year = $this->input->post('year');
            $user_id = $this->input->post('user_id');

            if (empty($user_id)) {
                redirect('att_rpt/lst', 'location');
            } else if ($user_id == 'ALL') {
                redirect(site_url("att_rpt/dtl_prsn_xls/" . $month . "_" . $year . "_ALL"), 'location');
            }

            //$jam_masuk = '07:40:00';
            //$jam_tengah = '12:00:00';

            $jam_masuk = '07:40';
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
              IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_masuk')) AS waktu_telat,
              IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_masuk', 1, 0) AS is_late,
              IF(MIN(io.check_time) = MAX(io.check_time), 1, 0) AS is_same,
              TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_masuk'))) AS sec_waktu_telat
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
            IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk')) AS waktu_telat,
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') > '$jam_masuk', 1, 0) AS is_late,
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') = DATE_FORMAT(MAX(io.check_time),'$time_format'), 1, 0) AS is_same,
            TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk'))) AS sec_waktu_telat,
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
    }

    /*public function save_ket() {
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
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
                } else {
                    $key_f = ($key < 10) ? "0" . $key : $key;

                    $str = "UPDATE keterangan SET expired_time = CURRENT_TIMESTAMP WHERE expired_time IS NULL AND user_id = $user_id AND DATE_FORMAT(tgl,'%d/%m/%Y') LIKE '$key_f/$month/$year'";

                    $query = $this->db->query($str);
                }
            }

            //$this->db->trans_complete();
            $data['month'] = $this->input->post('month');
            $data['year'] = $this->input->post('year');
            $data['user_id'] = $this->input->post('user_id');
            $this->load->view('ent_sv_ket', $data);
        }
    }*/
    
    public function detail_personal_monthly_all($month,$year) {
        //echo $month.' '.$year;
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $this->load->helper('file');
            delete_files("xls" . DIRECTORY_SEPARATOR . "dpm" . DIRECTORY_SEPARATOR, TRUE);
            
            $data['bln'] = $month;
            $data['thn'] = $year;
            $data['loading_msg'] = 'Membuat File Excel ';
            $data['success_msg'] = 'File Excel Berhasil Dibuat';
            $data['loading2_msg'] = 'Menampilkan Daftar File Excel ';
            //$data['success2_msg'] = 'Daftar File Excel Per Prodi/Bagian :';
            $this->load->view('rpt_dtl_prsn_mnthly_all',$data);
        }
    }
    
    public function detail_personal_monthly_all_files() {
        if ($this->session->userdata('username') == '') {
            echo 'Silahkan refresh halaman ini lagi';
        } else {
            $this->load->helper('directory');
            $map = directory_map("xls" . DIRECTORY_SEPARATOR . "dpm" . DIRECTORY_SEPARATOR);
            $list = "<code><ul>";
            foreach ($map as $m) {
                $list = $list . "<li><a href=\"" . base_url() . "xls/dpm/" . $m . "\">" . str_replace(".xls", "", $m) . "</a></li>";
            }
            $list = $list . "</ul></code>";
            echo $list;
        }
    }
    
    public function summary_department_yearly_all($year) {
        //echo $month.' '.$year;
        if ($this->session->userdata('username') == '') {
            redirect('login');
        } else {
            $this->load->helper('file');
            delete_files("xls" . DIRECTORY_SEPARATOR . "sdy" . DIRECTORY_SEPARATOR, TRUE);
            
            $data['thn'] = $year;
            $data['loading_msg'] = 'Membuat File Excel ';
            $data['success_msg'] = 'File Excel Berhasil Dibuat';
            $data['loading2_msg'] = 'Menampilkan Daftar File Excel ';
            //$data['success2_msg'] = 'Daftar File Excel Per Prodi/Bagian :';
            $this->load->view('rpt_sum_dept_yearly_all',$data);
        }
    }
    
    public function summary_department_yearly_all_files() {
        if ($this->session->userdata('username') == '') {
            echo 'Silahkan refresh halaman ini lagi';
        } else {
            $this->load->helper('directory');
            $map = directory_map("xls" . DIRECTORY_SEPARATOR . "sdy" . DIRECTORY_SEPARATOR);
            $list = "<code><ul>";
            foreach ($map as $m) {
                $list = $list . "<li><a href=\"" . base_url() . "xls/sdy/" . $m . "\">" . str_replace(".xls", "", $m) . "</a></li>";
            }
            $list = $list . "</ul></code>";
            echo $list;
        }
    }

}