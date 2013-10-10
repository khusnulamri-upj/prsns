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
$year = $arr[0];
$dept_id = $arr[1];

//$jam_telat_masuk = '07:40:00';
//$jam_tengah = '12:00:00';

$jam_masuk = '07:40';
$jam_tengah = '12:00';
$jam_keluar = '16:30';

//$time_format = "%T";
$time_format = "%H:%i";

//$filter_libur = array('Sat', 'Sun');
//$filter_mmyyyy = (($month < 10) ? "0" . $month : $month) . "/" . $year;

$limit_person = 5; //person per sheet

if ($dept_id == 'ALL') {
    ini_set('memory_limit', '-1');
    
    /** Error reporting */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    date_default_timezone_set('Europe/London');

    if (PHP_SAPI == 'cli')
        die('This example should only be run from a Web Browser');

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
        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
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
//start
        

//$dept_id = 23;
        
        $dept_id = $rowprodi['dept_id'];
        
        $sql22 = "SELECT u.user_id AS id
        FROM mdb_userinfo u
        WHERE u.default_dept_id = $dept_id
        ORDER BY u.name";

        $query22 = mysql_query($sql22);
        
        $ii = 0;

        $counter_p = 0;
        $index_p = 0;
        $arr_user_lst = array();
        while ($row22 = mysql_fetch_array($query22)) {
            $arr_user_lst[$index_p][$counter_p] = $row22['id'];
            if ($counter_p >= ($limit_person - 1)) {
                $counter_p = 0;
                $index_p++;
            } else {
                $counter_p++;
            }
        }

        $sheetkeberapa = 0;
        foreach ($arr_user_lst as $arr_lst) {
            $arr_user = $arr_lst;
            if ($sheetkeberapa > 0) {
                $objPHPExcel->createSheet();
            }

            $sqlqry = "SELECT u.dept_name AS nama
                FROM mdb_departments u
                WHERE u.dept_id = $dept_id
                LIMIT 1";

            $result = mysql_query($sqlqry) or die(mysql_error());

            $rowHeader = mysql_fetch_array($result) or die(mysql_error());

            $objPHPExcel->setActiveSheetIndex($sheetkeberapa)
                    ->setCellValue('A1', 'Laporan Kedatangan & Kepulangan Karyawan/Dosen')
                    ->setCellValue('A2', 'Prodi/Bagian')
                    ->setCellValue('C2', ': ' . $rowHeader['nama'])
                    ->setCellValue('A3', 'Tahun')
                    ->setCellValue('C3', ': ' . $year);

            $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
            $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
            $objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
            $objPHPExcel->getActiveSheet()->mergeCells('C2:F2');
            $objPHPExcel->getActiveSheet()->mergeCells('C3:F3');

            $objPHPExcel->getActiveSheet()->getStyle('A1:A3')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('C1:C3')->getFont()->setBold(true);

            //pojok bulan
            $objPHPExcel->getActiveSheet()->setCellValue('A5', 'BULAN');
            $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objPHPExcel->getActiveSheet()->mergeCells('A5:A7');
            $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->applyFromArray($styleThinBlackBorderOutline);

            $cell = 'A8';

            for ($i = 1; $i <= 12; $i++) {
                $str_bln = date("M", mktime(0, 0, 0, $i + 1, 0, 0));
                $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'R', $i - 1), $str_bln);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'R', $i - 1))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'R', $i - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'R', $i - 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            }

            $init_for_title = 'B5';
            $jumlah_kolom = 0;
            $col_cell = incCell($init_for_title, 'R', 1);

            foreach ($arr_user as $uu) {
                //initial
                $user_id = $uu;

                $sqlqry = "SELECT u.name AS nama
                    FROM mdb_userinfo u
                    WHERE u.user_id = $user_id
                    LIMIT 1";

                $result = mysql_query($sqlqry) or die(mysql_error());

                $rowHeader = mysql_fetch_array($result) or die(mysql_error());

                $objPHPExcel->getActiveSheet()->setCellValue($col_cell, strtoupper($rowHeader['nama']));
                $objPHPExcel->getActiveSheet()->getStyle($col_cell)->getFont()->setBold(true);
                $objPHPExcel->getActiveSheet()->getStyle($col_cell . ':' . incCell($col_cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->mergeCells($col_cell . ':' . incCell($col_cell, 'C', 2));
                $objPHPExcel->getActiveSheet()->getStyle($col_cell . ':' . incCell($col_cell, 'C', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $cell = incCell($col_cell, 'R', 1);

                $objPHPExcel->getActiveSheet()->setCellValue($cell, 'Hadir');
                $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 1), 'Terlambat');
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 2), 'Keterangan');
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $cell = incCell($cell, 'R', 1);

                for ($j = 1; $j <= 12; $j++) {

                    $bln = $j;

                    $filter_mmyyyy = (($bln < 10) ? "0" . $bln : $bln) . "/" . $year;

                    //HITUNG JUMLAH HADIR & TELAT
                    $sql = "select C.*, cc.content AS ket2_detail FROM (SELECT B.*, MAX(B.ket) AS ket2, IF((B.ket = 2) OR (B.ket = 1), 0, 1) AS counter
                        FROM (
                        SELECT A.*
                        FROM (
            SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') > '$jam_masuk', 1, 0) AS is_late,
            IF(DATE_FORMAT(MAX(io.check_time),'%H:%i') < '$jam_keluar', 1, 0) AS is_late2, 
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') = DATE_FORMAT(MAX(io.check_time),'$time_format'), 1, 0) AS is_same,
            NULL AS ket,
            NULL AS ket_detail,
            TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk'))) AS sec_waktu_telat
            FROM mdb_checkinout io
            WHERE io.user_id = $user_id
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
            GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ) A
            UNION
            SELECT k.user_id AS user_id,
            DATE_FORMAT(k.tgl,'%d/%m/%Y') AS tgl_presensi,
            NULL AS is_late,
            NULL AS is_late2,
            NULL AS is_same,
            k.opt_keterangan AS ket,
            o.content AS ket_detail,
            NULL AS sec_waktu_telat
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

                    $query = mysql_query($sql);

                    $ttl_telat = 0;
                    $ttl_hadir = 0;

                    while ($row = mysql_fetch_array($query)) {
                        if (($row['is_late'] == 1) && ($row['sec_waktu_telat'] > 60)) {
                            $ttl_telat++;
                        }
                        if ($row['counter'] == 1) {
                            $ttl_hadir++;
                        }
                    }

                    $sql2 = "SELECT o.opt_keterangan_id AS id, o.content AS keterangan, count(a.user_id) AS jumlah
                FROM opt_keterangan o
                LEFT OUTER JOIN (
                SELECT k.*
                FROM keterangan k
                WHERE k.user_id = $user_id
                AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
                AND k.expired_time IS NULL
                ) a ON o.opt_keterangan_id = a.opt_keterangan
                GROUP BY o.opt_keterangan_id";

                    $query2 = mysql_query($sql2);

                    $txt_notes = '';
                    $index = 0;
                    $arrKet = array();
                    $ttl_ijin = 0;
                    $is_ijin = false;
                    while ($row2 = mysql_fetch_array($query2)) {
                        $arrKet[$index] = new stdClass();

                        if (strpos(strtoupper($row2['keterangan']), 'IJIN') !== FALSE) {
                            $ttl_ijin = $row2['jumlah'] + $ttl_ijin;
                            $is_ijin = true;
                        } else {
                            if ($is_ijin) {
                                $arrKet[$index]->keterangan = 'Ijin';
                                $arrKet[$index]->jumlah = $ttl_ijin;
                                $index++;
                                $arrKet[$index] = new stdClass();
                                $arrKet[$index]->keterangan = $row2['keterangan'];
                                $arrKet[$index]->jumlah = $row2['jumlah'];
                                $ttl_ijin = 0;
                                $is_ijin = false;
                            } else {
                                $arrKet[$index]->keterangan = $row2['keterangan'];
                                $arrKet[$index]->jumlah = $row2['jumlah'];
                            }
                            $index++;
                        }
                    }

                    if ($is_ijin) {
                        $arrKet[$index]->keterangan = 'Ijin';
                        $arrKet[$index]->jumlah = $ttl_ijin;
                        $ttl_ijin = 0;
                    }
                    foreach ($arrKet as $a) {
                        $txt_notes = $txt_notes . $a->keterangan . " : " . $a->jumlah . "\n";
                    }

                    $txt_notes = substr($txt_notes, 0, (strlen($txt_notes) - 1));

                    //$objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 2), $cell.'-'.$filter_mmyyyy);

                    $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 0), $ttl_hadir);
                    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 0))->applyFromArray($styleThinBlackBorderOutline);
                    $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 1), $ttl_telat);
                    $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 1), 0, 1))->setWidth(15);
                    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
                    $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 2), $txt_notes);
                    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
                    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->getAlignment()->setWrapText(true);
                    $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 0), 0, 1))->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 1), 0, 1))->setWidth(10);
                    $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 2), 0, 1))->setAutoSize(true);

                    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 0) . ':' . incCell($cell, 'C', 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 0) . ':' . incCell($cell, 'C', 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                    $cell = incCell($cell, 'R', 1);
                }

                $jumlah_kolom++;
                $col_cell = incCell($col_cell, 'C', 3);
            }
            $end_for_title = incCell($init_for_title, 'C', ($jumlah_kolom * 3) - 1);
            $objPHPExcel->getActiveSheet()->setCellValue($init_for_title, 'PRESENSI KARYAWAN');
            $objPHPExcel->getActiveSheet()->getStyle($init_for_title)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($init_for_title)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $objPHPExcel->getActiveSheet()->mergeCells($init_for_title . ':' . $end_for_title);
            $objPHPExcel->getActiveSheet()->getStyle($init_for_title . ':' . $end_for_title)->applyFromArray($styleThinBlackBorderOutline);

            $objPHPExcel->getActiveSheet()->setTitle('H' . ($sheetkeberapa + 1));
            $sheetkeberapa++;
        }
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        //$objPHPExcel->setActiveSheetIndex(0);

        /** Include PHPExcel_IOFactory */
        require_once 'PHPExcel_Classes/PHPExcel/IOFactory.php';
        // Save Excel 95 file
        //echo date('H:i:s'), " Write to Excel5 format", EOL;
        //$callStartTime = microtime(true);
        
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
        $objWriter->save('../xls/sdy/' . $prodi_cleaned . '.xls');
        
    }
} else {
    ini_set('memory_limit', '-1');
    
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
    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
    $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(1);
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
//start
    

//$dept_id = 23;
    
    $sql22 = "SELECT u.user_id AS id
        FROM mdb_userinfo u
        WHERE u.default_dept_id = $dept_id
        ORDER BY u.name";

    $query22 = mysql_query($sql22);
    $ii = 0;
    
    //echo $sql22;
    
    $counter_p = 0;
    $index_p = 0;
    $arr_user_lst = array();
    while ($row22 = mysql_fetch_array($query22)) {
        $arr_user_lst[$index_p][$counter_p] = $row22['id'];
        if ($counter_p >= ($limit_person - 1)) {
            $counter_p = 0;
            $index_p++;
        } else {
            $counter_p++;
        }
    }

    $sheetkeberapa = 0;
    foreach ($arr_user_lst as $arr_lst) {
        $arr_user = $arr_lst;
        if ($sheetkeberapa > 0) {
            $objPHPExcel->createSheet();
        }

        $sqlqry = "SELECT u.dept_name AS nama
    FROM mdb_departments u
    WHERE u.dept_id = $dept_id
    LIMIT 1";

        $result = mysql_query($sqlqry) or die(mysql_error());

        $rowHeader = mysql_fetch_array($result) or die(mysql_error());

        $objPHPExcel->setActiveSheetIndex($sheetkeberapa)
                ->setCellValue('A1', 'Laporan Kedatangan & Kepulangan Karyawan/Dosen')
                ->setCellValue('A2', 'Prodi/Bagian')
                ->setCellValue('C2', ': ' . $rowHeader['nama'])
                ->setCellValue('A3', 'Tahun')
                ->setCellValue('C3', ': ' . $year);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
        $objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
        $objPHPExcel->getActiveSheet()->mergeCells('C2:F2');
        $objPHPExcel->getActiveSheet()->mergeCells('C3:F3');

        $objPHPExcel->getActiveSheet()->getStyle('A1:A3')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1:C3')->getFont()->setBold(true);

        //pojok bulan
        $objPHPExcel->getActiveSheet()->setCellValue('A5', 'BULAN');
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->mergeCells('A5:A7');
        $objPHPExcel->getActiveSheet()->getStyle('A5:A7')->applyFromArray($styleThinBlackBorderOutline);

        $cell = 'A8';

        for ($i = 1; $i <= 12; $i++) {
            $str_bln = date("M", mktime(0, 0, 0, $i + 1, 0, 0));
            $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'R', $i - 1), $str_bln);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'R', $i - 1))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'R', $i - 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'R', $i - 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        }

        $init_for_title = 'B5';
        $jumlah_kolom = 0;
        $col_cell = incCell($init_for_title, 'R', 1);

        foreach ($arr_user as $uu) {
            //initial
            $user_id = $uu;

            $sqlqry = "SELECT u.name AS nama
        FROM mdb_userinfo u
        WHERE u.user_id = $user_id
        LIMIT 1";

            $result = mysql_query($sqlqry) or die(mysql_error());

            $rowHeader = mysql_fetch_array($result) or die(mysql_error());

            $objPHPExcel->getActiveSheet()->setCellValue($col_cell, strtoupper($rowHeader['nama']));
            $objPHPExcel->getActiveSheet()->getStyle($col_cell)->getFont()->setBold(true);
            $objPHPExcel->getActiveSheet()->getStyle($col_cell . ':' . incCell($col_cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->mergeCells($col_cell . ':' . incCell($col_cell, 'C', 2));
            $objPHPExcel->getActiveSheet()->getStyle($col_cell . ':' . incCell($col_cell, 'C', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $cell = incCell($col_cell, 'R', 1);

            $objPHPExcel->getActiveSheet()->setCellValue($cell, 'Hadir');
            $objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 1), 'Terlambat');
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 2), 'Keterangan');
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $cell = incCell($cell, 'R', 1);

            for ($j = 1; $j <= 12; $j++) {

                $bln = $j;

                $filter_mmyyyy = (($bln < 10) ? "0" . $bln : $bln) . "/" . $year;

                //HITUNG JUMLAH HADIR & TELAT
                $sql = "select C.*, cc.content AS ket2_detail FROM (SELECT B.*, MAX(B.ket) AS ket2, IF((B.ket = 2) OR (B.ket = 1), 0, 1) AS counter
            FROM (
            SELECT A.*
            FROM (
            SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') > '$jam_masuk', 1, 0) AS is_late,
            IF(DATE_FORMAT(MAX(io.check_time),'%H:%i') < '$jam_keluar', 1, 0) AS is_late2, 
            IF(DATE_FORMAT(MIN(io.check_time),'$time_format') = DATE_FORMAT(MAX(io.check_time),'$time_format'), 1, 0) AS is_same,
            NULL AS ket,
            NULL AS ket_detail,
            TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'$time_format'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'$time_format')),'$jam_masuk'))) AS sec_waktu_telat
            FROM mdb_checkinout io
            WHERE io.user_id = $user_id
            AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
            GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
            ) A
            UNION
            SELECT k.user_id AS user_id,
            DATE_FORMAT(k.tgl,'%d/%m/%Y') AS tgl_presensi,
            NULL AS is_late,
            NULL AS is_late2,
            NULL AS is_same,
            k.opt_keterangan AS ket,
            o.content AS ket_detail,
            NULL AS sec_waktu_telat
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

                $query = mysql_query($sql);

                $ttl_telat = 0;
                $ttl_hadir = 0;

                while ($row = mysql_fetch_array($query)) {
                    if (($row['is_late'] == 1) && ($row['sec_waktu_telat'] > 60)) {
                        $ttl_telat++;
                    }
                    if ($row['counter'] == 1) {
                        $ttl_hadir++;
                    }
                }

                $sql2 = "SELECT o.opt_keterangan_id AS id, o.content AS keterangan, count(a.user_id) AS jumlah
                FROM opt_keterangan o
                LEFT OUTER JOIN (
                SELECT k.*
                FROM keterangan k
                WHERE k.user_id = $user_id
                AND DATE_FORMAT(k.tgl,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
                AND k.expired_time IS NULL
                ) a ON o.opt_keterangan_id = a.opt_keterangan
                GROUP BY o.opt_keterangan_id";

                $query2 = mysql_query($sql2);

                $txt_notes = '';
                $index = 0;
                $arrKet = array();
                $ttl_ijin = 0;
                $is_ijin = false;
                while ($row2 = mysql_fetch_array($query2)) {
                    $arrKet[$index] = new stdClass();

                    if (strpos(strtoupper($row2['keterangan']), 'IJIN') !== FALSE) {
                        $ttl_ijin = $row2['jumlah'] + $ttl_ijin;
                        $is_ijin = true;
                    } else {
                        if ($is_ijin) {
                            $arrKet[$index]->keterangan = 'Ijin';
                            $arrKet[$index]->jumlah = $ttl_ijin;
                            $index++;
                            $arrKet[$index] = new stdClass();
                            $arrKet[$index]->keterangan = $row2['keterangan'];
                            $arrKet[$index]->jumlah = $row2['jumlah'];
                            $ttl_ijin = 0;
                            $is_ijin = false;
                        } else {
                            $arrKet[$index]->keterangan = $row2['keterangan'];
                            $arrKet[$index]->jumlah = $row2['jumlah'];
                        }
                        $index++;
                    }
                }

                if ($is_ijin) {
                    $arrKet[$index]->keterangan = 'Ijin';
                    $arrKet[$index]->jumlah = $ttl_ijin;
                    $ttl_ijin = 0;
                }
                foreach ($arrKet as $a) {
                    $txt_notes = $txt_notes . $a->keterangan . " : " . $a->jumlah . "\n";
                }

                $txt_notes = substr($txt_notes, 0, (strlen($txt_notes) - 1));

                //$objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 2), $cell.'-'.$filter_mmyyyy);

                $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 0), $ttl_hadir);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 0))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 1), $ttl_telat);
                $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 1), 0, 1))->setWidth(15);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 1))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->setCellValue(incCell($cell, 'C', 2), $txt_notes);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->applyFromArray($styleThinBlackBorderOutline);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->getAlignment()->setWrapText(true);
                $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 0), 0, 1))->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 1), 0, 1))->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension(substr(incCell($cell, 'C', 2), 0, 1))->setAutoSize(true);

                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 0) . ':' . incCell($cell, 'C', 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 0) . ':' . incCell($cell, 'C', 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $cell = incCell($cell, 'R', 1);
            }

            $jumlah_kolom++;
            $col_cell = incCell($col_cell, 'C', 3);
        }
        $end_for_title = incCell($init_for_title, 'C', ($jumlah_kolom * 3) - 1);
        $objPHPExcel->getActiveSheet()->setCellValue($init_for_title, 'PRESENSI KARYAWAN');
        $objPHPExcel->getActiveSheet()->getStyle($init_for_title)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($init_for_title)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->mergeCells($init_for_title . ':' . $end_for_title);
        $objPHPExcel->getActiveSheet()->getStyle($init_for_title . ':' . $end_for_title)->applyFromArray($styleThinBlackBorderOutline);

        $objPHPExcel->getActiveSheet()->setTitle('H' . ($sheetkeberapa + 1));
        $sheetkeberapa++;
    }
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
//$objPHPExcel->setActiveSheetIndex(0);
// Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
//header('Content-Disposition: attachment;filename="ATTENDANCE_DEPT_YEAR_01.xls"');
    header('Content-Disposition: attachment;filename="sdy' . str_replace('_', '', $filter) . '.xls"');
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
