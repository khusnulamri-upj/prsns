<?php

$filename = "absenSep2013.xls";

$initialCell = "C6"; //inisialisasi cell pertama untuk BACA FILE
$cellToRight = 25; //berapa cell ke kanan untuk batas BACA FILE
$cellToBottom = -1; //berapa cell ke bawah untuk batas BACA FILE

$blankCell = 5; //ketika BACA FILE, berapa blank yang harus didapatkan agar BREAK

//mengambil koordinat huruf dan angka
$cellColRow = preg_split('/(?<=[A-Z])(?=[0-9]+)/', $initialCell);
$cellCol = $cellColRow[0];
$cellRow = $cellColRow[1];

$z = "AZ";
print(++$z);
print(++$z);

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

date_default_timezone_set('Asia/Jakarta');

/** Include PHPExcel_IOFactory */
require_once '/Classes/PHPExcel/IOFactory.php';

if (!file_exists($filename)) {
	exit($filename." doesn't exist." . EOL);
}

echo date('H:i:s') , " Load from Excel file" , EOL;
$callStartTime = microtime(true);

$objPHPExcel = PHPExcel_IOFactory::load($filename);

$cell = 'C6';
$cell++;
if (preg_match("/^[0-9]{2}\/[0-9]{2}\s[Sun|Mon|Tue|Wed|Thu|Fri|Sat]/", $objPHPExcel->getActiveSheet()->getCell($cell))) {
    echo $objPHPExcel->getActiveSheet()->getCell($cell);
}
else {
    echo "salah ".preg_match("/^[0-9]{2}/", $objPHPExcel->getActiveSheet()->getCell($cell));
}

$cellLine = substr($cell, 1, 1);
$cell = substr($cell, 0, 1);
$cell++;
$cell = $cell.$cellLine;
if (preg_match("/^[0-9]{2}\/[0-9]{2}\s[Sun|Mon|Tue|Wed|Thu|Fri|Sat]/", $objPHPExcel->getActiveSheet()->getCell($cell))) {
    echo $objPHPExcel->getActiveSheet()->getCell($cell);
}
else {
    echo "salah ".$cell.$objPHPExcel->getActiveSheet()->getCell($cell);
}

$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Test Amr');

$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;
echo 'Call time to read Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;


echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$callStartTime = microtime(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;

echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;


// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo done
echo date('H:i:s') , " Done writing file" , EOL;
echo 'File has been created in ' , getcwd() , EOL;
