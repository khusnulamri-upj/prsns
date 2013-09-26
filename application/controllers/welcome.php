<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Welcome extends CI_Controller {

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
        $this->load->database();
        
        $jam_telat_masuk = '07:40:00';
        $jam_tengah = '12:00:00';
        
        $data['filter_libur'] = array('Sat','Sun');
        
        $data['filter_mmyyyy'] = "09/2013";
        
        //$data['filter_mmyyyy'] = "09-2013";
        
        //echo days_in_month('06', '2005');

        /*$sql = "SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            DATE_FORMAT(MIN(io.check_time),'%T') AS jam_masuk,
            DATE_FORMAT(MAX(io.check_time),'%T') AS jam_keluar,
            IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_telat_masuk', 1, 0) as is_late
            FROM mdb_checkinout io
            WHERE io.user_id = 3
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/".$data['filter_mmyyyy']."'
            GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')";
        */
        $sql = "SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')) AS jam_masuk,
            IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'%T')) > 0,'',DATE_FORMAT(MAX(io.check_time),'%T')) AS jam_keluar,
            IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk')) AS waktu_telat,
            IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_telat_masuk', 1, 0) as is_late
            FROM mdb_checkinout io
            WHERE io.user_id = 1
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/".$data['filter_mmyyyy']."'
            GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')";
        
        /*$sql = "SELECT CONCAT(no.no_urut,'/9/2013') AS tgl, p.*
FROM temp_no_urut no
LEFT OUTER JOIN (
SELECT DATE_FORMAT(io.check_time,'%d') AS temp_tgl, io.user_id,
DATE_FORMAT(io.check_time,'%d-%m-%Y') AS tgl_presensi,
DATE_FORMAT(MIN(io.check_time),'%T') AS jam_masuk,
DATE_FORMAT(MAX(io.check_time),'%T') AS jam_keluar
FROM mdb_checkinout io
WHERE io.user_id = 3 AND DATE_FORMAT(io.check_time,'%d-%m-%Y') LIKE '%-09-2013".$data['filter_mmyyyy']."'
GROUP BY DATE_FORMAT(io.check_time,'%d-%m-%Y')
ORDER BY DATE_FORMAT(io.check_time,'%d-%m-%Y')
) p ON no.no_urut = p.temp_tgl";*/
        
        $query = $this->db->query($sql);

        $data['att_prsn'] = $query->result();
        $data['page_title'] = "CI Hello World App!";

        $this->load->view('welcome_message', $data);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */