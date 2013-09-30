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
                <?php
                echo form_open('att_rpt/dtl_prsn_sv_ket');
                echo form_hidden('user_id', $att_user_id);
                echo form_hidden('month', $att_mnth);
                echo form_hidden('year', $att_year);
                ?>
                <h3>Laporan Kedatangan dan Kepulangan Personil</h3>
                <table>
                    <tr><td style="width:50px;">Bagian</td><td>:</td><td><?= $att_dept ?></td></tr>
                    <tr><td>Nama</td><td>:</td><td><?= $att_nama ?></td></tr>
                    <tr><td>Bulan</td><td>:</td><td><?= date("F Y", mktime(0, 0, 0, $att_mnth + 1, 0, $att_year)) ?></td></tr>
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
                    $ttl_waktu_telat = null;
                    $ttl_telat = 0;
                    $ttl_hadir = 0;
                    $opt_ket[0] = "";
                    foreach ($att_opt_ket as $k) {
                        $opt_ket[$k->id] = $k->content;
                    }
                    //$arrtemp = explode('-', (isset($filter_mmyyyy)?$filter_mmyyyy:'09-2013'));
                    //$numdays = days_in_month($arrtemp[0], $arrtemp[1]); //input 06 2012
                    while ($i <= $att_loop) {
                        if ($i < 10) {
                            $tgl = alternator('01', '02', '03', '04', '05', '06', '07', '08', '09');
                        } else {
                            $tgl = $i;
                        }
                        $txtDay = mdate("%D", mktime(0, 0, 0, $att_mnth, $i, $att_year)); //input 1,2,10,11
                        $idx_drop = $i;
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
                                //if ($att_prsn[$j]->is_same) {
                                if (empty($att_prsn[$j]->jam_masuk) || empty($att_prsn[$j]->jam_keluar)) {
                                    $redText2 = "";
                                }
                                $ttl_telat++; 
                            } else {
                                $redText = "";
                                $redText2 = "";
                            }
                            
                            if (isset($att_prsn[$j]->ket)) {
                                $drop_ket = form_dropdown('ket['.$idx_drop.']', $opt_ket, $att_prsn[$j]->ket);
                            } else {
                                $drop_ket = null;
                            }
                            
                            //echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->waktu_telat . "</td><td class=\"lstc\"".$redText2.">" . ($att_prsn[$j]->is_same ? "TIDAK LENGKAP" : ($att_prsn[$j]->is_late ? "TERLAMBAT" : "")) . "</td></tr>";
                            if (isset($drop_ket)) {
                                echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . substr($att_prsn[$j]->waktu_telat,0,5) . "</td><td class=\"lstc\"".$redText2.">" . $drop_ket . "</td></tr>";
                            } else {
                                echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . substr($att_prsn[$j]->waktu_telat,0,5) . "</td><td class=\"lstc\"".$redText2.">" . (empty($att_prsn[$j]->jam_masuk) || empty($att_prsn[$j]->jam_keluar) ? "TIDAK LENGKAP" : "") . "</td></tr>";
                                //echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . substr($att_prsn[$j]->waktu_telat,0,5) . "</td><td class=\"lstc\"".$redText2.">" . ($att_prsn[$j]->is_same ? "TIDAK LENGKAP" : "") . "</td></tr>";
                            }
                            $drop_ket = "";
                            if ($att_prsn[$j]->is_late) {
                                $ttl_waktu_telat = $ttl_waktu_telat+$att_prsn[$j]->sec_waktu_telat;
                            }
                            $ttl_hadir++; 
                            if ($a >= $j) {
                                $j++;
                            }
                        } else {
                            if ($libur == "LIBUR") {
                                //$liburRow = " style=\"color: #C00000; font-weight:bold;\"";
                                $liburRow = " style=\"color: white; background-color: #C00000; font-weight:bold; opacity:0.7;\"";
                                echo "<tr".$liburRow."><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
                            } else {
                                //echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
                                //print_r($att_opt_ket);
                                echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">".form_dropdown('ket['.$idx_drop.']', $opt_ket)."</td></tr>"; 
                            }
                        }
                    }
                    echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan=\"2\" class=\"lstc\">Total Durasi Keterlambatan</td><td class=\"lstc\">".mdate("%H:%i", mktime(0, 0, (empty($ttl_waktu_telat)?0:$ttl_waktu_telat), 0, 0, 0))."</td></tr>";
                    echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan=\"2\" class=\"lstc\">Total Keterlambatan (hari)</td><td class=\"lstc\">$ttl_telat</td></tr>";
                    echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan=\"2\" class=\"lstc\">Total Kehadiran (hari)</td><td class=\"lstc\">$ttl_hadir</td></tr>";
                    ?>
                </table>
                <p style="font-size: 4px;"><?= $att_kode ?></p>
                <p><?= form_submit('save', 'Simpan'); ?></p>
                <p><a href="<?= site_url("att_rpt/dtl_prsn_xls/$att_filter"); ?>">Eksport ke XLS</a></p>
                <p><a href="<?= site_url("att_rpt/lst"); ?>">Kembali</a></p>
                <?= form_close(); ?>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>