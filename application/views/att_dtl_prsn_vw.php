<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>

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
                color: #002166;
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

            table.lst, td.lstc, th.lsth
            {
                border-collapse:collapse;
                border:1px solid #D0D0D0;
                padding:5px;
                text-align:center;
            }
            th.lsth
            {
                background-color:#f9f9f9;
            }
        </style>
    </head>
    <body>
        <div id="container">
            <h1>Attendance Information System</h1>

            <div id="body">
                <h3>Laporan Kedatangan dan Kepulangan Personil</h3>
                <table>
                    <tr><td style="width:50px;">Bagian</td><td>:</td><td><?= $att_dept ?></td></tr>
                    <tr><td>Nama</td><td>:</td><td><?= $att_nama ?></td></tr>
                </table>
                <br/>
                <table class="lst">
                    <tr>
                        <th class="lsth">Tanggal</th><th class="lsth">Hari</th><th class="lsth">Jam Masuk</th><th class="lsth">Jam Keluar</th><th class="lsth" style="width:90px;">Durasi Keterlambatan</th><th class="lsth" style="width:110px;">Keterangan</th>
                    </tr>
                    <?php
                    $a = sizeof($att_prsn);
                    $i = 1;
                    $j = 0;
                    //$arrtemp = explode('-', (isset($filter_mmyyyy)?$filter_mmyyyy:'09-2013'));
                    //$numdays = days_in_month($arrtemp[0], $arrtemp[1]); //input 06 2012
                    while ($i <= $att_loop) {
                        if ($i < 10) {
                            $tgl = alternator('01', '02', '03', '04', '05', '06', '07', '08', '09');
                        } else {
                            $tgl = $i;
                        }
                        $txtDay = mdate("%D", mktime(0, 0, 0, $att_mnth, $i, $att_year)); //input 1,2,10,11
                        $i++;
                        $full_date = $tgl . "/" . $filter_mmyyyy;

                        $compare = isset($att_prsn[$j]->tgl_presensi) ? $att_prsn[$j]->tgl_presensi : '';

                        if (in_array($txtDay, $filter_libur)) {
                            $libur = "LIBUR";
                        } else {
                            $libur = "";
                        }

                        if ($full_date === $compare) {
                            if ($att_prsn[$j]->is_late) {
                                $redText = " style=\"color: #C00000;\"";
                                $redText2 = " style=\"color: #C00000;\"";
                                if ($att_prsn[$j]->is_same) {
                                    $redText2 = "";
                                }
                            } else {
                                $redText = "";
                                $redText2 = "";
                            }
                            
                            echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->waktu_telat . "</td><td class=\"lstc\"".$redText2.">" . ($att_prsn[$j]->is_same ? "TIDAK LENGKAP" : ($att_prsn[$j]->is_late ? "TERLAMBAT" : "")) . "</td></tr>";
                            if ($a >= $j) {
                                $j++;
                            }
                        } else {
                            if ($libur == "LIBUR") {
                                //$liburRow = " style=\"color: #C00000; font-weight:bold;\"";
                                $liburRow = " style=\"color: white; background-color: #C00000; font-weight:bold; opacity:0.7;\"";
                                echo "<tr".$liburRow."><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
                            } else {
                                echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
                            }
                        }
                    }
                    ?>
                </table>
                <p style="font-size: 4px;"><?= $att_kode ?></p>
                <p><a href="<?= site_url("att_rpt/dtl_prsn_xls/$att_filter"); ?>">Eksport ke XLS</a></p>
                <p><a href="<?= site_url("att_rpt/lst"); ?>">Kembali</a></p>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>