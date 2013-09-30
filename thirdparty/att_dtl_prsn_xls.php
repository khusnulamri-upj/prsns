<?php

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
    
    return $cellCol.$cellRow;
}

$con = mysql_connect("localhost", "root", "Upeje2013");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }

$db_selected = mysql_select_db("presensi",$con);

$filter = $_GET['fltr'];

$arr = explode('_',$filter);
$month = $arr[0];
$year = $arr[1];
$user_id = $arr[2];

$jam_telat_masuk = '07:40:00';
$jam_tengah = '12:00:00';

$filter_libur = array('Sat', 'Sun');
$filter_mmyyyy = (($month < 10) ? "0" . $month : $month) . "/" . $year;

$sqlqry = "SELECT u.name AS nama, d.dept_name AS dept
    FROM mdb_userinfo u
    LEFT OUTER JOIN mdb_departments d ON u.default_dept_id = d.dept_id
    WHERE u.user_id = $user_id
    LIMIT 1"; 
	 
$result = mysql_query($sqlqry) or die(mysql_error());

$rowHeader = mysql_fetch_array($result) or die(mysql_error());

/*$sql = "SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
    IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')) AS jam_masuk,
    IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'%T')) > 0,'',DATE_FORMAT(MAX(io.check_time),'%T')) AS jam_keluar,
    IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk')) AS waktu_telat,
    IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_telat_masuk', 1, 0) AS is_late,
    IF(MIN(io.check_time) = MAX(io.check_time), 1, 0) AS is_same
    FROM mdb_checkinout io
    WHERE io.user_id = $user_id
    AND DATE_FORMAT(io.check_time,'%d/%m/%Y') LIKE '%/" . $filter_mmyyyy . "'
    GROUP BY DATE_FORMAT(io.check_time,'%d/%m/%Y')
    ORDER BY DATE_FORMAT(io.check_time,'%d/%m/%Y')";*/

$sql = " SELECT B.* 
    FROM (
    SELECT A.*
    FROM (
    SELECT io.user_id, DATE_FORMAT(io.check_time,'%d/%m/%Y') AS tgl_presensi, 
    IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')) AS jam_masuk,
    IF(TIMEDIFF('$jam_tengah',DATE_FORMAT(MAX(io.check_time),'%T')) > 0,'',DATE_FORMAT(MAX(io.check_time),'%T')) AS jam_keluar,
    IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk')) AS waktu_telat,
    IF(DATE_FORMAT(MIN(io.check_time),'%T') > '$jam_telat_masuk', 1, 0) AS is_late,
    IF(DATE_FORMAT(MIN(io.check_time),'%T') = DATE_FORMAT(MAX(io.check_time),'%T'), 1, 0) AS is_same,
    TIME_TO_SEC(IF(TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk') < 0,'',TIMEDIFF(IF(TIMEDIFF(DATE_FORMAT(MIN(io.check_time),'%T'),'$jam_tengah') > 0,'',DATE_FORMAT(MIN(io.check_time),'%T')),'$jam_telat_masuk'))) AS sec_waktu_telat,
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
    ORDER BY B.tgl_presensi";

$query = mysql_query($sql);

$index = 0;

while ($row = mysql_fetch_array($query)) {
    $resultArr[$index]->tgl_presensi = $row['tgl_presensi'];
    $resultArr[$index]->jam_masuk = $row['jam_masuk'];
    $resultArr[$index]->jam_keluar = $row['jam_keluar'];
    $resultArr[$index]->waktu_telat = $row['waktu_telat'];
    $resultArr[$index]->is_late = $row['is_late'];
    $resultArr[$index]->is_same = $row['is_same'];
    $resultArr[$index]->sec_waktu_telat = $row['sec_waktu_telat'];
    $resultArr[$index]->ket = $row['ket'];
    $index++;
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
        ->setTitle("Laporan Kedatangan dan Kepulangan Personil")
        ->setCategory("Attendance Report");

$styleThinBlackBorderOutline = array(
    'borders' => array(
        'outline' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('argb' => 'FF000000'),
        ),
    ),
);

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', 'Laporan Kedatangan dan Kepulangan Personil')
        ->setCellValue('A2', 'Prodi/Bagian')
        ->setCellValue('C2', ': '.$rowHeader['dept'])
        ->setCellValue('A3', 'Nama')
        ->setCellValue('C3', ': '.$rowHeader['nama']);

$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
$objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
$objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
$objPHPExcel->getActiveSheet()->mergeCells('C2:F2');
$objPHPExcel->getActiveSheet()->mergeCells('C3:F3');

$objPHPExcel->getActiveSheet()->getStyle('A1:A3')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('C2:C3')->getFont()->setBold(true);

$objPHPExcel->getActiveSheet()
        ->setCellValue('A5', 'Tanggal')
        ->setCellValue('B5', 'Hari')
        ->setCellValue('C5', 'Jam Masuk')
        ->setCellValue('D5', 'Jam Keluar')
        ->setCellValue('E5', 'Durasi Keterlambatan')
        ->setCellValue('F5', 'Keterangan');

$objPHPExcel->getActiveSheet()->getStyle('E5')->getAlignment()->setWrapText(true);

$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$objPHPExcel->getActiveSheet()->getStyle('A5:F5')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle('C5')->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle('D5')->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle('E5')->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle('F5')->applyFromArray($styleThinBlackBorderOutline);

$cell = 'A6'; //initial cell

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
        $tgl = "0".$i;
    } else {
        $tgl = $i;
    }
    $i++;
    $full_date = $tgl . "/" . $filter_mmyyyy;
    //hati2 format input jadi mm/dd/yyyy
    $month_formatted = ($month < 10) ? "0".$month:$month;
    $date_formatted = $month_formatted."/".$tgl."/".$year;
    $txtDay = substr(date('l', strtotime($date_formatted)),0,3); //input 1,2,10,11

    $compare = isset($resultArr[$j]->tgl_presensi) ? $resultArr[$j]->tgl_presensi : '';

    if (in_array($txtDay, $filter_libur)) {
        $libur = "LIBUR";
    } else {
        $libur = "";
    }

    if ($full_date === $compare) {
        $col1 = $compare;
        $col2 = $txtDay;
        $col3 = $resultArr[$j]->jam_masuk;
        $col4 = $resultArr[$j]->jam_keluar;
        $col5 = $resultArr[$j]->waktu_telat;
        $col6 = (empty($resultArr[$j]->ket)?(empty($resultArr[$j]->jam_masuk) || empty($resultArr[$j]->jam_keluar) ? "TIDAK LENGKAP" : ""):$resultArr[$j]->ket);
        //$col6 = (empty($resultArr[$j]->ket)?($resultArr[$j]->is_same ? "TIDAK LENGKAP" : ""):$resultArr[$j]->ket);
        if ($resultArr[$j]->is_late) {
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 2))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 4))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            $ttl_telat++;
            $ttl_waktu_telat = $ttl_waktu_telat+$resultArr[$j]->sec_waktu_telat;
        }
        $ttl_hadir++;
        if ($a >= $j) {
            $j++;
        }
    } else {
        if ($libur == "LIBUR") {
            $objPHPExcel->getActiveSheet()->getStyle($cell.":".incCell($cell, 'C', 5))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objPHPExcel->getActiveSheet()->getStyle($cell.":".incCell($cell, 'C', 5))->getFill()->getStartColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            $objPHPExcel->getActiveSheet()->getStyle($cell.":".incCell($cell, 'C', 5))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
        }
        /*else {
            echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
        }*/
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
    $objPHPExcel->getActiveSheet()->getStyle($cell.":".incCell($cell, 'C', 4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    
    $cell = incCell($cell, 'R', 1);
}

$objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3).":".incCell(incCell($cell, 'C', 5),'R',2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3).':'.incCell($cell, 'C', 4));
$objPHPExcel->getActiveSheet()
            ->setCellValue(incCell($cell, 'C', 3), 'Total Durasi Keterlambatan')   //keluar
            ->setCellValue(incCell($cell, 'C', 5), date("H:i", mktime(0, 0, (empty($ttl_waktu_telat)?0:$ttl_waktu_telat), 0, 0, 0)));  //ket
$objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3).":".incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
$cell = incCell($cell, 'R', 1);

$objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3).':'.incCell($cell, 'C', 4));
$objPHPExcel->getActiveSheet()
            ->setCellValue(incCell($cell, 'C', 3), 'Total Keterlambatan (hari)')   //keluar
            ->setCellValue(incCell($cell, 'C', 5), $ttl_telat);  //ket
$objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3).":".incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
$cell = incCell($cell, 'R', 1);

$objPHPExcel->getActiveSheet()->mergeCells(incCell($cell, 'C', 3).':'.incCell($cell, 'C', 4));
$objPHPExcel->getActiveSheet()
            ->setCellValue(incCell($cell, 'C', 3), 'Total Kehadiran (hari)')   //keluar
            ->setCellValue(incCell($cell, 'C', 5), $ttl_hadir);  //ket
$objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 3).":".incCell($cell, 'C', 4))->applyFromArray($styleThinBlackBorderOutline);
$objPHPExcel->getActiveSheet()->getStyle(incCell($cell, 'C', 5))->applyFromArray($styleThinBlackBorderOutline);
$cell = incCell($cell, 'R', 1);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
//$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getRowDimension('5')->setRowHeight(30);

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('attrpt0A.'.$filter);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
//$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="attrpt0A.xls"');
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
