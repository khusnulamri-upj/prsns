<?php

$filename = 'absenSep2013.xls';

$initialCell = 'C6'; //inisialisasi cell pertama untuk BACA FILE
$cellToRight = 25; //berapa cell ke kanan untuk batas BACA FILE
$cellToBottom = -1; //berapa cell ke bawah untuk batas BACA FILE

$blankCell = 9; //KOLOM --ketika BACA FILE, berapa blank yang harus didapatkan agar BREAK
$blankRow = 3; //BARIS

//mengambil koordinat huruf dan angka
//$cellColRow = preg_split('/(?<=[A-Z])(?=[0-9]+)/', $initialCell);
//$cellCol = $cellColRow[0];
//$cellRow = $cellColRow[1];

//$z = 'AA';
//echo ++$z , EOL;

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

//PHPExcel mulai
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

//mulai parsing
$cell = $initialCell;

$nextRow = 1; //berapa row apabila sudah limit ke kanan

$currBlankRow = 0; //baris blank yang telah ditemukan

$iArrTimeSheet = 0;

while ($currBlankRow <= $blankRow) {
    
    $colReaded = 0;
    
    $timeSheetReaded = false;
    $dataPersonReaded = false;
    
    $tempCell = $cell;
    
    $currBlankCol = 0;
    
    echo "<b>" , date('H:i:s') , " START ROW " , $cell , "</b>" ,EOL;
    
    while ($currBlankCol <= $blankCell) {
        //echo date('H:i:s') , " START COL " , $cell , EOL;
        
        $value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
        
        echo date('H:i:s') , " START COL " , $cell , $value , EOL;
        
        //BACA CELL ke kanan
        if (($value == '') || (empty($value))) {
            $currBlankCol = $currBlankCol+1;
            echo date('H:i:s') , " currBlankCol -".$currBlankCol."- " , $cell , "(".$value.")" , EOL;
        } else {
            $colReaded = $colReaded+1;
            $currBlankCol = 0;
            if (preg_match("/^[0-9]{2}\/[0-9]{2}\s[Sun|Mon|Tue|Wed|Thu|Fri|Sat]/", $value)) { //format: dd/mm day
                echo date('H:i:s') , " <i>PRODI DATA COL</i> " , $prodi , EOL;
                echo date('H:i:s'), " <i>ID DATA COL</i> ", $id, EOL;
                echo date('H:i:s'), " <i>NAMA DATA COL</i> ", $nama, EOL;
                $timeSheetReaded = true;
                $dateCell = $value;
                $timeCell = $objPHPExcel->getActiveSheet()->getCell(incCell($cell, 'R', 1))->getValue();
                
                //$arrTimeSheet[$id][$iArrTimeSheet] = array('temp_prodi' => $prodi, 'temp_id' => $id, 'temp_name' => $nama, 'temp_tgl' => $dateCell, 'temp_jam' => $timeCell);
                //tidak pake array id
                $arrTimeSheet[$iArrTimeSheet] = array('temp_prodi' => $prodi, 'temp_id' => $id, 'temp_name' => $nama, 'temp_tgl' => $dateCell, 'temp_jam' => $timeCell);
                
                $iArrTimeSheet++;
                echo date('H:i:s') , " PRESENSI COL ?".$dateCell."? ?".$timeCell."?" , $cell , EOL;
            } else {
                $dataPersonReaded = true;
                echo date('H:i:s') , " DATA COL " , $cell , EOL;
                if (empty($prodi)) {
                    $prodi = $value;
                    echo date('H:i:s') , " <i>PRODI DATA COL</i> " , $prodi , EOL;
                } else {
                    if (empty($id)) {
                        $id = $value;
                        echo date('H:i:s'), " <i>ID DATA COL</i> ", $id, EOL;
                    } else {
                        $nama = $value;
                        echo date('H:i:s'), " <i>NAMA DATA COL</i> ", $nama, EOL;
                    }
                }
            }
        }

        $cell = incCell($cell, 'C', 1);
        echo date('H:i:s') , " [".$currBlankCol."] " , "(".$value.")" , " NEXT COL " , $cell , EOL;
    }
    
    if ($colReaded > 0) {
        $currBlankRow = 0;
    } else {
        $currBlankRow = $currBlankRow+1;
    }
    
    if ($timeSheetReaded) {
        $nextRow = 2;
    } else {
        $nextRow = 1;
        if (!$dataPersonReaded) {
            $prodi = null;
            $id = null;
            $nama = null;
        } else {
            //$iArrTimeSheet = 0; //tidak pake array id
        }
    }
    
    echo date('H:i:s') , " [".$currBlankRow."]" , " FINISH ROW " , $cell , EOL;
    
    $cell = incCell($tempCell, 'R', $nextRow);
    
}

//print_r($arrTimeSheet[25]);

/*$cell++;

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
}*/

//$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Test Amr');

$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;
echo 'Call time to read Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;


/*echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$callStartTime = microtime(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($filename.'');

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
echo 'File has been created in ' , getcwd() , EOL;*/

//TAMBAHAN
//dapat tahun
$value = $objPHPExcel->getActiveSheet()->getCell('T2')->getValue();
$array = explode(' ', $value);
$arr = explode('-', $array[2]);
$year = null;
foreach ($arr as $a) {
    $b = intval($a);
    if ($b > 31) {
        $year = $b;
    } else if ($b <= 12) {
        $numMonth = $b;
        $month = date("M", $b);
    } else {
        $date = $b;
    }
}
if (empty($year)) {
    $year = date("Y");
}

if (empty($month)) {
    $month = date("M");
}

include_once 'connectdb.php';

$i = 0;

foreach ($arrTimeSheet as $row) {
    /*$sql = "INSERT INTO temp_fingerprint (prodi, id, nama, tanggal, jam)
            VALUES
            ('".$row['temp_prodi']."','".$row['temp_id']."','".$row['temp_name']."','".$year."/".$row['temp_tgl']."','".$row['temp_jam']."')";
    */
    
    $arrTgl1 = explode(" ", $row['temp_tgl']);
    $arrTgl2 = explode("/", $arrTgl1[0]);
    
    $sql = "INSERT INTO temp_fingerprint2 (prodi, id, nama, tanggal, jam)
            VALUES
            ('".$row['temp_prodi']."','".$row['temp_id']."','".$row['temp_name']."','".$year."-".$arrTgl2[0]."-".$arrTgl2[1]."','".$row['temp_jam']."')";
    
    echo $sql, EOL;
    
    if (!mysql_query($sql)) {
        die('Error: ROW '.$i);
    }
    $i++;
}

echo $i." record added";

mysql_close($con);

echo now(), "\n";