<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Import extends CI_Controller {

    public function index() {
        $config['hostname'] = "Driver={Microsoft Access Driver (*.mdb)};DBQ=D:\UPJ\Attendance\att2000.mdb";
        $config['username'] = "";
        $config['password'] = "";
        $config['database'] = "Driver={Microsoft Access Driver (*.mdb)};DBQ=D:\UPJ\Attendance\att2000.mdb";
        $config['dbdriver'] = "odbc";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";

        $this->load->database($config);

        $query = $this->db->query("SELECT * FROM CHECKINOUT");

        foreach ($query->result() as $row) {
            echo $row->USERID;
            //echo $row->body;
        }

        $this->db->close();

        //$this->load->view('welcome_message');
    }

    public function setting() {
        //$this->session->set_userdata('import_mdb_file_path', 'some_value');
    }

    public function mdb() {
        //$file_path = $this->session->userdata('import_mdb_file_path');

        $file_path = "D:\UPJ\Attendance\att2000.mdb";

        $config['hostname'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['username'] = "";
        $config['password'] = "";
        $config['database'] = "Driver={Microsoft Access Driver (*.mdb)}; DBQ=" . $file_path;
        $config['dbdriver'] = "odbc";
        $config['dbprefix'] = "";
        $config['pconnect'] = FALSE;
        $config['db_debug'] = TRUE;
        $config['cache_on'] = FALSE;
        $config['cachedir'] = "";
        $config['char_set'] = "utf8";
        $config['dbcollat'] = "utf8_general_ci";

        $db_mdb = $this->load->database($config, TRUE);
        $db_mysql = $this->load->database('default', TRUE);
        
        echo "load 2 db finish";
        
        //$qry_mdb = $db_mdb->query("SELECT USERID AS user_id, CHECKTIME AS check_time FROM CHECKINOUT");
        /*$sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT 
            WHERE DATEVALUE(CHECKTIME) >= '12/1/2013' AND DATEVALUE(CHECKTIME) <= '12/31/2013'";
        */
        $sql_mdb = "SELECT USERID AS user_id, CHECKTIME AS check_time, 
            CHECKTYPE AS check_type, VERIFYCODE AS verify_code, SENSORID AS sensor_id, 
            WORKCODE AS work_code, sn 
            FROM CHECKINOUT"; 
        //    WHERE USERID >= 116 AND USERID <= 135";
        
        /*$sql_mdb = "SELECT DEPTID AS dept_id, DEPTNAME AS dept_name, SUPDEPTID AS sup_dept_id, 
            InheritParentSch AS inherit_parent_sch, InheritDeptSch AS inherit_dept_sch, 
            InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
            InLate AS in_late, OutEarly AS out_early, InheritDeptRule AS inherit_dept_rule, 
            MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
            DefaultSchId AS default_sch_id, att, holiday, OverTime AS over_time 
            FROM DEPARTMENTS";
        */
        /*$sql_mdb = "SELECT USERID AS user_id, Badgenumber AS badge_number, ssn, name, gender, title, 
           pager, BIRTHDAY AS birth_day, HIREDDAY AS hired_day, street, city, state, zip, 
           OPHONE AS o_phone, FPHONE AS f_phone, VERIFICATIONMETHOD AS verification_method, 
           DEFAULTDEPTID AS default_dept_id, SECURITYFLAGS AS security_flags, att, INLATE AS in_late, 
           OUTEARLY AS out_early, overtime, sep, holiday, minzu, password, LUNCHDURATION AS lunch_duration, 
           MVERIFYPASS AS m_verify_pass, photo, notes, privilege, InheritDeptSch AS inherit_dept_sch, 
           InheritDeptSchClass AS inherit_dept_sch_class, AutoSchPlan AS auto_sch_plan, 
           MinAutoSchInterval AS min_auto_sch_interval, RegisterOT AS register_ot, 
           InheritDeptRule AS inherit_dept_rule, emprivilege, CardNo AS card_no, pin1
           FROM USERINFO";
        */
        $qry_mdb = $db_mdb->query($sql_mdb);
        
        echo "query mdb finish next trans";
        
        $db_mysql->trans_start();
        
        foreach ($qry_mdb->result() as $row_mdb) {
            $data_mysql = array(
                'user_id' => $row_mdb->user_id,
                'check_time' => $row_mdb->check_time,
                'check_type' => $row_mdb->check_type,
                'verify_code' => $row_mdb->verify_code,
                'sensor_id' => $row_mdb->sensor_id,
                'work_code' => $row_mdb->work_code,
                'sn' => $row_mdb->sn
            );
            
            /*$data_mysql = array(
                'dept_id' => $row_mdb->dept_id,
                'dept_name' => $row_mdb->dept_name,
                'sup_dept_id' => $row_mdb->sup_dept_id,
                'inherit_parent_sch' => $row_mdb->inherit_parent_sch,
                'inherit_dept_sch' => $row_mdb->inherit_dept_sch,
                'inherit_dept_sch_class' => $row_mdb->inherit_dept_sch_class,
                'auto_sch_plan' => $row_mdb->auto_sch_plan,
                'in_late' => $row_mdb->in_late,
                'out_early' => $row_mdb->out_early,
                'inherit_dept_rule' => $row_mdb->inherit_dept_rule,
                'min_auto_sch_interval' => $row_mdb->min_auto_sch_interval,
                'register_ot' => $row_mdb->register_ot,
                'default_sch_id' => $row_mdb->default_sch_id,
                'att' => $row_mdb->att,
                'holiday' => $row_mdb->holiday,
                'over_time' => $row_mdb->over_time
            );
            */
            /*$data_mysql = array(
                'user_id' => $row_mdb->user_id,
                'badge_number' => $row_mdb->badge_number,
                'ssn' => $row_mdb->ssn,
                'name' => $row_mdb->name,
                'gender' => $row_mdb->gender,
                'title' => $row_mdb->title,
                'pager' => $row_mdb->pager,
                'birth_day' => $row_mdb->birth_day,
                'hired_day' => $row_mdb->hired_day,
                'street' => $row_mdb->street,
                'city' => $row_mdb->city,
                'state' => $row_mdb->state,
                'zip' => $row_mdb->zip,
                'o_phone' => $row_mdb->o_phone,
                'f_phone' => $row_mdb->f_phone,
                'verification_method' => $row_mdb->verification_method,
                'default_dept_id' => $row_mdb->default_dept_id,
                'security_flags' => $row_mdb->security_flags,
                'att' => $row_mdb->att,
                'in_late' => $row_mdb->in_late,
                'out_early' => $row_mdb->out_early,
                'overtime' => $row_mdb->overtime,
                'sep' => $row_mdb->sep,
                'holiday' => $row_mdb->holiday,
                'minzu' => $row_mdb->minzu,
                'password' => $row_mdb->password,
                'lunch_duration' => $row_mdb->lunch_duration,
                'm_verify_pass' => $row_mdb->m_verify_pass,
                'photo' => $row_mdb->photo,
                'notes' => $row_mdb->notes,
                'privilege' => $row_mdb->privilege,
                'inherit_dept_sch' => $row_mdb->inherit_dept_sch,
                'inherit_dept_sch_class' => $row_mdb->inherit_dept_sch_class,
                'auto_sch_plan' => $row_mdb->auto_sch_plan,
                'min_auto_sch_interval' => $row_mdb->min_auto_sch_interval,
                'register_ot' => $row_mdb->register_ot,
                'inherit_dept_rule' => $row_mdb->inherit_dept_rule,
                'emprivilege' => $row_mdb->emprivilege,
                'card_no' => $row_mdb->card_no,
                'pin1' => $row_mdb->pin1               
            );
            */
            
            print_r($data_mysql);
            
            $db_mysql->insert('mdb_checkinout', $data_mysql);
            //$db_mysql->insert('mdb_departments', $data_mysql);
            //$db_mysql->insert('mdb_userinfo', $data_mysql);
            
            //echo "insert data to mysql finish ".$row_mdb->user_id;
        }
        
        $db_mysql->trans_complete();
        
        $db_mdb->close();
        $db_mysql->close();

        //$this->load->view('welcome_message');
    }

}