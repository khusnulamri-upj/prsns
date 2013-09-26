<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>

	<style type="text/css">

	::selection{ background-color: #E13300; color: white; }
	::moz-selection{ background-color: #E13300; color: white; }
	::webkit-selection{ background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body{
		margin: 0 15px 0 15px;
	}
	
	p.footer{
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}
	
	#container{
		margin: 10px;
		border: 1px solid #D0D0D0;
		-webkit-box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
</head>
<body>

<div id="container">
	<h1>Welcome to CodeIgniter!</h1>

	<div id="body">
		<p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

		<p>If you would like to edit this page you'll find it located at:</p>
		<code>application/views/welcome_message.php</code>

		<p>The corresponding controller for this page is found at:</p>
		<code>application/controllers/welcome.php</code>

		<p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="user_guide/">User Guide</a>.</p>
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

<table>
<?php
$a = sizeof($att_prsn);
$i = 1;
$j = 0;
//$arrtemp = explode('-', (isset($filter_mmyyyy)?$filter_mmyyyy:'09-2013'));
//$numdays = days_in_month($arrtemp[0], $arrtemp[1]); //input 06 2012
while ($i <= 31) {
    if ($i < 10) {
        $tgl = alternator('01','02','03','04','05','06','07','08','09');
    } else {
        $tgl = $i;
    }
    $txtDay = mdate("%D", mktime(0, 0, 0, 9, $i, 2013)); //input 1,2,10,11
    $i++;
    $full_date = $tgl."/".$filter_mmyyyy;
    
    $compare = isset($att_prsn[$j]->tgl_presensi)?$att_prsn[$j]->tgl_presensi: '';
    
    if (in_array($txtDay, $filter_libur)) {
        $libur = "LIBUR";
    } else {
        $libur = "";
    }
    
    if ($full_date === $compare) {
        echo "<tr><td>$compare</td><td>$txtDay</td><td>".$att_prsn[$j]->jam_masuk."</td><td>".$att_prsn[$j]->jam_keluar."</td><td>".$att_prsn[$j]->waktu_telat."</td><td>".($att_prsn[$j]->is_late?"TERLAMBAT":"")."</td></tr>";
        if ($a >= $j) {
            $j++;
        }
    } else {
        echo "<tr><td>$full_date</td><td>$txtDay</td><td>$libur</td><td>$libur</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
    }
}

print_r($att_prsn);
?>
</table>
</body>
</html>