<?php

set_time_limit(0);

ini_set('memory_limit', '-1');

function incCell($inCell, $mode, $numInc = 1) {
    $cellColRow = preg_split('/(?<=[A-Z])(?=[0-9]+)/', $inCell);
    $cellCol = $cellColRow[0];
    $cellRow = $cellColRow[1];

    $i = 1;
    while ($i <= $numInc) {
        if ($mode == 'R') {
            $cellRow++;
        } else {
            $cellCol++;
        }
        $i++;
    }

    return $cellCol . $cellRow;
}

$con = mysql_connect("localhost", "root", "Upeje2013");
if (!$con) {
    die('Could not connect: ' . mysql_error());
}

$db_selected = mysql_select_db("presensi", $con);

$filter = $_GET['fltr'];

$arr = explode('_', $filter);
//echo $filter;
$month = $arr[0];
$year = $arr[1];
$user_id = $arr[2];

//$jam_telat_masuk = '07:40:00';
//$jam_tengah = '12:00:00';

$jam_telat_masuk = '07:40';
$jam_tengah = '12:00';
$jam_keluar = '16:30';

$jam_masuk = $jam_telat_masuk;

//$time_format = "%T";
$time_format = "%H:%i";

$filter_libur = array('Sat', 'Sun');
$filter_mmyyyy = (($month < 10) ? "0" . $month : $month) . "/" . $year;

if ($user_id == 'ALL') {
    ini_set('memory_limit', '-1');
    /** Error reporting */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);

    define('EOL', (PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

    date_default_timezone_set('Europe/London');

    /** Include PHPExcel */
    require_once 'PHPExcel_Classes/PHPExcel.php';

    $sqlprodi = "SELECT dept_id, dept_name
        FROM mdb_departments";
    
    /*$sqlprodi = "SELECT d.dept_id, d.dept_name
            FROM mdb_userinfo u
            LEFT OUTER JOIN mdb_departments d ON u.default_dept_id = d.dept_id
            GROUP BY d.dept_id
            ORDER BY d.dept_name";*/

    $resultprodi = mysql_query($sqlprodi) or die(mysql_error());

    while ($rowprodi = mysql_fetch_array($resultprodi)) {
        unset($objPHPExcel);

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Universitas Pembangunan Jaya")
                ->setLastModifiedBy("ICT")
                ->setTitle("Laporan Kedatangan & Kepulangan Karyawan/Dosen")
                ->setCategory("Report");

        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );

        $sheetke = -1;
        //mengambil semua user id
        //$sqlall = "SELECT user_id FROM mdb_userinfo WHERE default_dept_id = 1";
        $sqlall = "SELECT user_id FROM mdb_userinfo WHERE default_dept_id = " . $rowprodi['dept_id'];
        
        $resultall = mysql_query($sqlall) or die(mysql_error());
        
        while ($rowAll = mysql_fetch_array($resultall)) {
            $sheetke++;
            if ($sheetke > 0) {
                $objPHPExcel->createSheet();
            }
            $user_id = $rowAll['user_id'];

            //echo $user_id;

            $sqlqry = "SELECT u.name AS nama, d.dept_name AS dept
            FROM mdb_userinfo u
            LEFT OUTER JOIN mdb_departments d ON u.default_dept_id = d.dept_id
            WHERE u.user_id = $user_id
            LIMIT 1";

            $result = mysql_query($sqlqry) or die(mysql_error());

            $rowHeader = mysql_fetch_array($result) or die(mysql_error());

            $sql = "select C.*, cc.content AS ket2_detail FROM (SELECT B.*, MAX(B.ket) AS ket2, IF((B.ket = 2) OR (B.ket = 1), 0, 1) AS counter
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
            NULL AS ket,
            NULL AS ket_detail
            FROM mdb_checkinout io
            WHERE io.user_id = $user_id
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
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
            k.opt_keterangan AS ket,
            o.content AS ket_detail
            FROM keterangan k
            LEFT OUTER JOIN opt_keterangan o ON k.opt_keterangan = o.opt_keterangan_id
            WHERE k.user_id = $user_id
            AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
            AND k.expired_time IS NULL
            GROUP BY DATE_FORMAT(k.tgl,'%d/%m/%Y')
            ) B
            GROUP BY B.tgl_presensi
            ORDER BY B.tgl_presensi
            ) C
            LEFT OUTER JOIN opt_keterangan cc ON c.ket2 = cc.opt_keterangan_id";

            //echo $sql;

            $query = mysql_query($sql);

            $index = 0;
            $resultArr = array();

            while ($row = mysql_fetch_array($query)) {
                $resultArr[$index] = new stdClass();
                $resultArr[$index]->user_id = $row['user_id'];
                $resultArr[$index]->tgl_presensi = $row['tgl_presensi'];
                $resultArr[$index]->jam_masuk = $row['jam_masuk'];
                $resultArr[$index]->jam_keluar = $row['jam_keluar'];
                $resultArr[$index]->waktu_telat = $row['waktu_telat'];
                $resultArr[$index]->is_late = $row['is_late'];
                $resultArr[$index]->is_late2 = $row['is_late2'];
                $resultArr[$index]->is_same = $row['is_same'];
                $resultArr[$index]->sec_waktu_telat = $row['sec_waktu_telat'];
                $resultArr[$index]->ket = $row['ket'];
                $resultArr[$index]->ket_detail = $row['ket_detail'];
                $resultArr[$index]->ket2 = $row['ket2'];
                $resultArr[$index]->counter = $row['counter'];
                $resultArr[$index]->ket2_detail = $row['ket2_detail'];
                $index++;
            }

            $sql2 = "SELECT o.opt_keterangan_id AS id, o.content AS keterangan, count(a.user_id) AS jumlah
                FROM opt_keterangan o
                LEFT OUTER JOIN (
                SELECT k.*
                FROM keterangan k
                WHERE k.user_id = $user_id
                AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/$filter_mmyyyy'
                AND k.expired_time IS NULL
                ) a ON o.opt_keterangan_id = a.opt_keterangan
                GROUP BY o.opt_keterangan_id";

            $query2 = mysql_query($sql2);

            $index2 = 0;
            $resultArr2 = array();

            while ($row2 = mysql_fetch_array($query2)) {
                $resultArr2[$index2] = new stdClass();
                $resultArr2[$index2]->keterangan = $row2['keterangan'];
                $resultArr2[$index2]->jumlah = $row2['jumlah'];
                $index2++;
            }
            // Add some data

            $nama_edited = ucwords(strtolower(trim($rowHeader['nama'])));
            $months_indonesia = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
            if (($month >= 0) && ($month <= 12)) {
                $month_edited = $months_indonesia[$month - 1];
            } else {
                $month_edited = NULL;
            }
            $objPHPExcel->setActiveSheetIndex($sheetke)
                    ->setCellValue('A1', 'Laporan Kedatangan & Kepulangan Karyawan/Dosen')
                    ->setCellValue('A2', 'Prodi/Bagian')
                    ->setCellValue('C2', ': ' . $rowHeader['dept'])
                    ->setCellValue('A3', 'Nama')
                    ->setCellValue('C3', ': ' . $nama_edited)
                    ->setCellValue('A4', 'Bulan')
                    ->setCellValue('C4', ': ' . $month_edited . ' ' . $year);
            //date("F Y", mktime(0, 0, 0, $month + 1, 0, $year)));

            $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
            $objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
            $objPHPExcel->getActiveSheet()->mergeCells('A4:B4');
            $objPHPExcel->getActiveSheet()->mergeCells('C2:F2');
            $objPHPExcel->getActiveSheet()->mergeCells('C3:F3');
            $objPHPExcel->getActiveSheet()->mergeCells('C4:F4');

            $objPHPExcel->getActiveSheet()->getStyle('A1:A4')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('C2:C4')->getFont()->setBold(true);

            $objPHPExcel->getActiveSheet()
                    ->setCellValue('A6', 'Tanggal')
                    ->setCellValue('B6', 'Hari')
                    ->setCellValue('C6', 'Jam Masuk')
                    ->setCellValue('D6', 'Jam Keluar')
                    ->setCellValue('E6', 'Durasi Keterlambatan')
                    ->setCellValue('F6', 'Keterangan');

            $objPHPExcel->getActiveSheet()->getStyle('E6')->getAlignment()->setWrapText(true);

            $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray($styleThinBlackBorderOutline);

            $cell = 'A7'; //initial cell

            $att_loop = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $a = sizeof($resultArr);
            $i = 1;
            $j = 0;
            $ttl_hadir = 0;
            $ttl_telat = 0;
            $ttl_waktu_telat = 0;
//$arrtemp = explode('-', (isset($filter_mmyyyy)?$filter_mmyyyy:'09-2013'));
//$numdays = days_in_month($arrtemp[0], $arrtemp[1]); //input 06 2012
            while ($i <= $att_loop) {
                if ($i < 10) {
                    $tgl = "0" . $i;
                } else {
                    $tgl = $i;
                }
                $i++;
                $full_date = $tgl . "/" . $filter_mmyyyy;
                //hati2 format input jadi mm/dd/yyyy
                $month_formatted = ($month < 10) ? "0" . $month : $month;
                $date_formatted = $month_formatted . "/" . $tgl . "/" . $year;
                $txtDay = substr(date('l', strtotime($date_formatted)), 0, 3); //input 1,2,10,11

                $compare = isset($resultArr[$j]->tgl_presensi) ? $resultArr[$j]->tgl_presensi : '';

                if (in_array($txtDay, $filter_libur)) {
                    $libur = "LIBUR";
                } else {
                    $libur = "";
                }

                if ($full_date === $compare) {
                    if ($resultArr[$j]->ket != '') {
                        $keterangan = $resultArr[$j]->ket_detail;
                    } else if ($resultArr[$j]->ket2 != '') {
                        $keterangan = $resultArr[$j]->ket2_detail;
                    } else {
                        $keterangan = '';
                    }

                    $col1 = $compare;
                    $col2 = $txtDay;
                    $col3 = $resultArr[$j]->jam_masuk;
                    $col4 = $resultArr[$j]->jam_keluar;
                    $col5 = (($resultArr[$j]->waktu_telat != '') AND (substr($resultArr[$j]->waktu_telat, 0, 5) != '00:00')) ? substr($resultArr[$j]->waktu_telat, 0, 5) : '';
                    //$col6 = (empty($resultArr[$j]->ket_detail) ? (empty($resultArr[$j]->jam_masuk) || empty($resultArr[$j]->jam_keluar) ? "TIDAK LENGKAP" : "") : $resultArr[$j]->ket);
                    //$col6 = (empty($resultArr[$j]->ket)?($resultArr[$j]->is_same ? "TIDAK LENGKAP" : ""):$resultArr[$j]->ket);
                    $col6 = $keterangan;
                    if ($col5 != '') {
                        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 4))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                        $ttl_telat++;
                        $ttl_waktu_telat = $ttl_waktu_telat + $resultArr[$j]->sec_waktu_telat;
                    }
                    if ($resultArr[$j]->is_late2) {
                        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                    }
                    if ($resultArr[$j]->counter) {
                        $ttl_hadir++;
                    }

                    if ($a >= $j) {
                        $j++;
                    }
                } else {
                    if ($libur == "LIBUR") {
                        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 5))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 5))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 5))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
                    }
                    /* else {
                      echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
                      } */
                    $col1 = $full_date;
                    $col2 = $txtDay;
                    $col3 = $libur;
                    $col4 = $libur;
                    $col5 = '';
                    $col6 = '';
                }

                $objPHPExcel->getActiveSheet()
                        ->setCellValue($cell, $col1)                    //tgl
                        ->setCellValue(incCell($cell, 'C', 1), $col2)   //day
                        ->setCellValue(incCell($cell, 'C', 2), $col3)   //masuk
                        ->setCellValue(incCell($cell, 'C', 3), $col4)   //keluar
                        ->setCellValue(incCell($cell, 'C', 4), $col5)   //durasi telat
                        ->setCellValue(incCell($cell, 'C', 5), $col6);  //ket

                $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                $cell = incCell($cell, 'R', 1);
            }

            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell(incCell($cell, 'C', 5), 'R', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3) . ':' . incCell($cell, 'C', 4));
            $objPHPExcel->getActiveSheet()
                    ->setCellValue(incCell($cell, 'C', 3), 'Total Durasi Keterlambatan')   //keluar
                    ->setCellValue(incCell($cell, 'C', 5), date("H:i", mktime(0, 0, (empty($ttl_waktu_telat) ? 0 : $ttl_waktu_telat), 0, 0, 0)));  //ket
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
            $cell = incCell($cell, 'R', 1);

            $objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3) . ':' . incCell($cell, 'C', 4));
            $objPHPExcel->getActiveSheet()
                    ->setCellValue(incCell($cell, 'C', 3), 'Total Keterlambatan (hari)')   //keluar
                    ->setCellValue(incCell($cell, 'C', 5), $ttl_telat);  //ket
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
            $cell = incCell($cell, 'R', 1);

            $objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3) . ':' . incCell($cell, 'C', 4));
            $objPHPExcel->getActiveSheet()
                    ->setCellValue(incCell($cell, 'C', 3), 'Total Kehadiran (hari)')   //keluar
                    ->setCellValue(incCell($cell, 'C', 5), $ttl_hadir);  //ket
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);

            //SUMMARY
            $cell = incCell($cell, 'R', 2);
            $objPHPExcel->getActiveSheet()
                    ->setCellValue($cell, 'KETERANGAN');
            $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
            $cell = incCell($cell, 'R', 1);

            foreach ($resultArr2 as $r) {
                $objPHPExcel->getActiveSheet()->mergeCells($cell . ':' . incCell($cell, 'C', 2));
                $objPHPExcel->getActiveSheet()
                        ->setCellValue($cell, $r->keterangan)
                        ->setCellValue(incCell($cell, 'C', 3), ': ' . $r->jumlah);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
                $cell = incCell($cell, 'R', 1);
            }


            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
//$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(30);

// Rename worksheet
            //$objPHPExcel->getActiveSheet()->setTitle('H'.($sheetke+1));
            $arr_first_words = explode(' ',trim($nama_edited));
            $objPHPExcel->getActiveSheet()->setTitle(($sheetke+1).'-'.$arr_first_words[0]);
            
            //unset($resultArr);
        }

        /** Include PHPExcel_IOFactory */
        require_once 'PHPExcel_Classes/PHPExcel/IOFactory.php';
        // Save Excel 95 file
        //echo date('H:i:s'), " Write to Excel5 format", EOL;
        $callStartTime = microtime(true);
        
        $prodi_cleaned = $rowprodi['dept_name'];
        
        $char_must_cleaned = array(
            array('char_search' => '&', 'char_replace' => ' '),
            array('char_search' => '.', 'char_replace' => ''),
            array('char_search' => ',', 'char_replace' => '')
        );
        
        foreach ($char_must_cleaned as $c) {
            $prodi_cleaned = str_replace($c['char_search'], $c['char_replace'], $prodi_cleaned);
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //$objWriter->save('../xls/' . $year . $month_formatted . '_' . $prodi_cleaned . '.xls');
        $objWriter->save('../xls/dpm/' . $prodi_cleaned . '.xls');
        $callEndTime = microtime(true);
        $callTime = $callEndTime - $callStartTime;

        //echo date('H:i:s'), " File written to ", str_replace('.php', '.xls', pathinfo($year.'_'.$month_formatted.'_'.$rowprodi['dept_name'].'.xls', PATHINFO_BASENAME)), EOL;
        //echo date('H:i:s'), ' ' . pathinfo($year . '_' . $month_formatted . '_' . $rowprodi['dept_name'] . '.xls', PATHINFO_BASENAME), " Created ", EOL;
        //echo 'Call time to write Workbook was ', sprintf('%.4f', $callTime), " seconds", EOL;
// Echo memory usage
        //echo date('H:i:s'), ' Current memory usage: ', (memory_get_usage(true) / 1024 / 1024), " MB", EOL;
    }
    //echo "<p><a href=\"../xls\">Lihat Files</a></p>";
    //echo "<p><a href=\"../index.php/menu\">Kembali</a></p>";
} else {

    $sqlqry = "SELECT u.name AS nama, d.dept_name AS dept
    FROM mdb_userinfo u
    LEFT OUTER JOIN mdb_departments d ON u.default_dept_id = d.dept_id
    WHERE u.user_id = $user_id
    LIMIT 1";

    $result = mysql_query($sqlqry) or die(mysql_error());

    $rowHeader = mysql_fetch_array($result) or die(mysql_error());

    /* $sql = "SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
      IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')) AS jam_masuk,
      IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'%T')) > 0,'',DATE_FORMAT(MAX(io.check_time),'%T')) AS jam_keluar,
      IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk')) AS waktu_telat,
      IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_telat_masuk', 1, 0) AS is_late,
      IF(MIN(io.check_time) = MAX(io.check_time), 1, 0) AS is_same
      FROM mdb_checkinout io
      WHERE io.user_id = $user_id
      AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
      GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
      ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')"; */

    /* $sql = " SELECT B.* 
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
      AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
      GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
      ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
      ) A
      UNION
      SELECT
      k.user_id AS user_id,
      DATE_FORMAT(k.tgl,'%d/%m/%Y') AS tgl_presensi,
      NULL AS jam_masuk,
      NULL AS jam_keluar,
      NULL AS waktu_telat,
      NULL AS is_late,
      NULL AS is_same,
      NULL AS sec_waktu_telat,
      o.content AS ket
      FROM keterangan k
      LEFT OUTER JOIN opt_keterangan o ON k.opt_keterangan = o.opt_keterangan_id
      WHERE k.user_id = $user_id
      AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%" . $filter_mmyyyy . "'
      AND k.expired_time IS NULL
      GROUP BY DATE_FORMAT(k.tgl,'%d/%m/%Y')
      ) B
      ORDER BY B.tgl_presensi"; */

    $sql = "select C.*, cc.content AS ket2_detail FROM (SELECT B.*, MAX(B.ket) AS ket2, IF((B.ket = 2) OR (B.ket = 1), 0, 1) AS counter
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
            NULL AS ket,
            NULL AS ket_detail
            FROM mdb_checkinout io
            WHERE io.user_id = $user_id
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
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
            k.opt_keterangan AS ket,
            o.content AS ket_detail
            FROM keterangan k
            LEFT OUTER JOIN opt_keterangan o ON k.opt_keterangan = o.opt_keterangan_id
            WHERE k.user_id = $user_id
            AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
            AND k.expired_time IS NULL
            GROUP BY DATE_FORMAT(k.tgl,'%d/%m/%Y')
            ) B
            GROUP BY B.tgl_presensi
            ORDER BY B.tgl_presensi
            ) C
            LEFT OUTER JOIN opt_keterangan cc ON c.ket2 = cc.opt_keterangan_id";

    //echo $sql;

    $query = mysql_query($sql);

    $index = 0;
    $resultArr = array();

    while ($row = mysql_fetch_array($query)) {
        $resultArr[$index] = new stdClass();
        $resultArr[$index]->user_id = $row['user_id'];
        $resultArr[$index]->tgl_presensi = $row['tgl_presensi'];
        $resultArr[$index]->jam_masuk = $row['jam_masuk'];
        $resultArr[$index]->jam_keluar = $row['jam_keluar'];
        $resultArr[$index]->waktu_telat = $row['waktu_telat'];
        $resultArr[$index]->is_late = $row['is_late'];
        $resultArr[$index]->is_late2 = $row['is_late2'];
        $resultArr[$index]->is_same = $row['is_same'];
        $resultArr[$index]->sec_waktu_telat = $row['sec_waktu_telat'];
        $resultArr[$index]->ket = $row['ket'];
        $resultArr[$index]->ket_detail = $row['ket_detail'];
        $resultArr[$index]->ket2 = $row['ket2'];
        $resultArr[$index]->counter = $row['counter'];
        $resultArr[$index]->ket2_detail = $row['ket2_detail'];
        $index++;
    }

    $sql2 = "SELECT o.opt_keterangan_id AS id, o.content AS keterangan, count(a.user_id) AS jumlah
                FROM opt_keterangan o
                LEFT OUTER JOIN (
                SELECT k.*
                FROM keterangan k
                WHERE k.user_id = $user_id
                AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/$filter_mmyyyy'
                AND k.expired_time IS NULL
                ) a ON o.opt_keterangan_id = a.opt_keterangan
                GROUP BY o.opt_keterangan_id";

    $query2 = mysql_query($sql2);

    $index2 = 0;
    $resultArr2 = array();

    while ($row2 = mysql_fetch_array($query2)) {
        $resultArr2[$index2] = new stdClass();
        $resultArr2[$index2]->keterangan = $row2['keterangan'];
        $resultArr2[$index2]->jumlah = $row2['jumlah'];
        $index2++;
    }

    /** Error reporting */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    date_default_timezone_set('Europe/London');

    if (PHP_SAPI == 'cli')
        die('This example should only be run from a Web Browser');

    /** Include PHPExcel */
    require_once 'PHPExcel_Classes/PHPExcel.php';


// Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

// Set document properties
    $objPHPExcel->getProperties()->setCreator("Universitas Pembangunan Jaya")
            ->setLastModifiedBy("ICT")
            ->setTitle("Laporan Kedatangan & Kepulangan Karyawan/Dosen")
            ->setCategory("Report");

    $styleThinBlackBorderOutline = array(
        'borders' => array(
            'outline' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => 'FF000000'),
            ),
        ),
    );

// Add some data


    $nama_edited = ucwords(strtolower(trim($rowHeader['nama'])));
    $months_indonesia = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    if (($month >= 0) && ($month <= 12)) {
        $month_edited = $months_indonesia[$month - 1];
    } else {
        $month_edited = NULL;
    }
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Laporan Kedatangan & Kepulangan Karyawan/Dosen')
            ->setCellValue('A2', 'Prodi/Bagian')
            ->setCellValue('C2', ': ' . $rowHeader['dept'])
            ->setCellValue('A3', 'Nama')
            ->setCellValue('C3', ': ' . $nama_edited)
            ->setCellValue('A4', 'Bulan')
            ->setCellValue('C4', ': ' . $month_edited . ' ' . $year);
    //date("F Y", mktime(0, 0, 0, $month + 1, 0, $year)));

    $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
    $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
    $objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
    $objPHPExcel->getActiveSheet()->mergeCells('A4:B4');
    $objPHPExcel->getActiveSheet()->mergeCells('C2:F2');
    $objPHPExcel->getActiveSheet()->mergeCells('C3:F3');
    $objPHPExcel->getActiveSheet()->mergeCells('C4:F4');

    $objPHPExcel->getActiveSheet()->getStyle('A1:A4')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('C2:C4')->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()
            ->setCellValue('A6', 'Tanggal')
            ->setCellValue('B6', 'Hari')
            ->setCellValue('C6', 'Jam Masuk')
            ->setCellValue('D6', 'Jam Keluar')
            ->setCellValue('E6', 'Durasi Keterlambatan')
            ->setCellValue('F6', 'Keterangan');

    $objPHPExcel->getActiveSheet()->getStyle('E6')->getAlignment()->setWrapText(true);

    $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A6:F6')->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray($styleThinBlackBorderOutline);

    $cell = 'A7'; //initial cell

    $att_loop = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $a = sizeof($resultArr);
    $i = 1;
    $j = 0;
    $ttl_hadir = 0;
    $ttl_telat = 0;
    $ttl_waktu_telat = 0;
//$arrtemp = explode('-', (isset($filter_mmyyyy)?$filter_mmyyyy:'09-2013'));
//$numdays = days_in_month($arrtemp[0], $arrtemp[1]); //input 06 2012
    while ($i <= $att_loop) {
        if ($i < 10) {
            $tgl = "0" . $i;
        } else {
            $tgl = $i;
        }
        $i++;
        $full_date = $tgl . "/" . $filter_mmyyyy;
        //hati2 format input jadi mm/dd/yyyy
        $month_formatted = ($month < 10) ? "0" . $month : $month;
        $date_formatted = $month_formatted . "/" . $tgl . "/" . $year;
        $txtDay = substr(date('l', strtotime($date_formatted)), 0, 3); //input 1,2,10,11

        $compare = isset($resultArr[$j]->tgl_presensi) ? $resultArr[$j]->tgl_presensi : '';

        if (in_array($txtDay, $filter_libur)) {
            $libur = "LIBUR";
        } else {
            $libur = "";
        }

        if ($full_date === $compare) {
            if ($resultArr[$j]->ket != '') {
                $keterangan = $resultArr[$j]->ket_detail;
            } else if ($resultArr[$j]->ket2 != '') {
                $keterangan = $resultArr[$j]->ket2_detail;
            } else {
                $keterangan = '';
            }

            $col1 = $compare;
            $col2 = $txtDay;
            $col3 = $resultArr[$j]->jam_masuk;
            $col4 = $resultArr[$j]->jam_keluar;
            $col5 = (($resultArr[$j]->waktu_telat != '') AND (substr($resultArr[$j]->waktu_telat, 0, 5) != '00:00')) ? substr($resultArr[$j]->waktu_telat, 0, 5) : '';
            //$col6 = (empty($resultArr[$j]->ket_detail) ? (empty($resultArr[$j]->jam_masuk) || empty($resultArr[$j]->jam_keluar) ? "TIDAK LENGKAP" : "") : $resultArr[$j]->ket);
            //$col6 = (empty($resultArr[$j]->ket)?($resultArr[$j]->is_same ? "TIDAK LENGKAP" : ""):$resultArr[$j]->ket);
            $col6 = $keterangan;
            if ($col5 != '') {
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 4))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                $ttl_telat++;
                $ttl_waktu_telat = $ttl_waktu_telat + $resultArr[$j]->sec_waktu_telat;
            }
            if ($resultArr[$j]->is_late2) {
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            }
            if ($resultArr[$j]->counter) {
                $ttl_hadir++;
            }

            if ($a >= $j) {
                $j++;
            }
        } else {
            if ($libur == "LIBUR") {
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 5))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 5))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 5))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
            }
            /* else {
              echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
              } */
            $col1 = $full_date;
            $col2 = $txtDay;
            $col3 = $libur;
            $col4 = $libur;
            $col5 = '';
            $col6 = '';
        }

        $objPHPExcel->getActiveSheet()
                ->setCellValue($cell, $col1)                    //tgl
                ->setCellValue(incCell($cell, 'C', 1), $col2)   //day
                ->setCellValue(incCell($cell, 'C', 2), $col3)   //masuk
                ->setCellValue(incCell($cell, 'C', 3), $col4)   //keluar
                ->setCellValue(incCell($cell, 'C', 4), $col5)   //durasi telat
                ->setCellValue(incCell($cell, 'C', 5), $col6);  //ket

        $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $cell = incCell($cell, 'R', 1);
    }

    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell(incCell($cell, 'C', 5), 'R', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3) . ':' . incCell($cell, 'C', 4));
    $objPHPExcel->getActiveSheet()
            ->setCellValue(incCell($cell, 'C', 3), 'Total Durasi Keterlambatan')   //keluar
            ->setCellValue(incCell($cell, 'C', 5), date("H:i", mktime(0, 0, (empty($ttl_waktu_telat) ? 0 : $ttl_waktu_telat), 0, 0, 0)));  //ket
    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
    $cell = incCell($cell, 'R', 1);

    $objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3) . ':' . incCell($cell, 'C', 4));
    $objPHPExcel->getActiveSheet()
            ->setCellValue(incCell($cell, 'C', 3), 'Total Keterlambatan (hari)')   //keluar
            ->setCellValue(incCell($cell, 'C', 5), $ttl_telat);  //ket
    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
    $cell = incCell($cell, 'R', 1);

    $objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3) . ':' . incCell($cell, 'C', 4));
    $objPHPExcel->getActiveSheet()
            ->setCellValue(incCell($cell, 'C', 3), 'Total Kehadiran (hari)')   //keluar
            ->setCellValue(incCell($cell, 'C', 5), $ttl_hadir);  //ket
    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3) . ":" . incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);

    //SUMMARY
    $cell = incCell($cell, 'R', 2);
    $objPHPExcel->getActiveSheet()
            ->setCellValue($cell, 'KETERANGAN');
    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
    $cell = incCell($cell, 'R', 1);

    foreach ($resultArr2 as $r) {
        $objPHPExcel->getActiveSheet()->mergeCells($cell . ':' . incCell($cell, 'C', 2));
        $objPHPExcel->getActiveSheet()
                ->setCellValue($cell, $r->keterangan)
                ->setCellValue(incCell($cell, 'C', 3), ': ' . $r->jumlah);
        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle($cell . ":" . incCell($cell, 'C', 3))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
        $cell = incCell($cell, 'R', 1);
    }


    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
//$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(30);
    
    

// Rename worksheet
    //$objPHPExcel->getActiveSheet()->setTitle('attrpt0A.' . $filter);
    $arr_first_words = explode(' ',trim($nama_edited));
    $objPHPExcel->getActiveSheet()->setTitle($arr_first_words[0]);
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
//$objPHPExcel->setActiveSheetIndex(0);
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    //header('Content-Disposition: attachment;filename="attrpt0A.xls"');
    header('Content-Disposition: attachment;filename="dpm' . str_replace('_', '', $filter) . '.xls"');
    header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

