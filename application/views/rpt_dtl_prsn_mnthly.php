<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>

        <link rel="stylesheet" type="text/css" href="<?= base_url()."files/css/style.css"; ?>">
        
    </head>
    <body>
        <?php $this->load->view('includes/topinfo'); ?>
        <div id="container">
            <h1>Attendance Information System</h1>

            <div id="body">
                <?php
                echo form_open('entry/save_ket');
                echo form_hidden('user_id', $att_user_id);
                echo form_hidden('month', $att_mnth);
                echo form_hidden('year', $att_year);
                ?>
                <h3>Laporan Kedatangan dan Kepulangan Karyawan/Dosen</h3>
                <table>
                    <tr><td style="width:70px;">Prodi/Bagian</td><td>:</td><td><?= $att_dept ?></td></tr>
                    <tr><td>Nama</td><td>:</td><td><?= ucwords(strtolower($att_nama)) ?></td></tr>
                    <tr><td>Bulan</td><td>:</td><td>
                        <?php
                        //echo date("F Y", mktime(0, 0, 0, $att_mnth + 1, 0, $att_year));
                        echo $att_mnth_name.' '.$att_year;
                        ?>
                        </td></tr>
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
                                //$redText2 = " style=\"color: #C00000;\"";
                                $redText2 = "";
                                //if ($att_prsn[$j]->is_same) {
                                if (empty($att_prsn[$j]->jam_masuk) || empty($att_prsn[$j]->jam_keluar)) {
                                    $redText2 = "";
                                }
                                
                            } else {
                                $redText = "";
                                $redText2 = "";
                            }
                            
                            if ($att_prsn[$j]->is_late2) {
                                $redText3 = " style=\"color: #C00000;\"";
                            } else {
                                $redText3 = "";
                            }
                            
                            if (isset($att_prsn[$j]->ket)) {
                                //$drop_ket = form_dropdown('ket['.$idx_drop.']', $opt_ket, $att_prsn[$j]->ket);
                                $drop_ket = $att_prsn[$j]->ket;
                                foreach ($att_resume as $k) {
                                    if ($k->id == $drop_ket) {
                                        $drop_ket = $k->keterangan;
                                    }
                                }
                            } else if (empty($att_prsn[$j]->jam_masuk) || empty($att_prsn[$j]->jam_keluar)) {
                                //$drop_ket = form_dropdown('ket['.$idx_drop.']', $opt_ket, $att_prsn[$j]->ket2);
                                $drop_ket = $att_prsn[$j]->ket2;
                                foreach ($att_resume as $k) {
                                    if ($k->id == $drop_ket) {
                                        $drop_ket = $k->keterangan;
                                    }
                                }
                            } else if (($att_prsn[$j]->is_late) || ($att_prsn[$j]->is_late2)) {
                                //$drop_ket = form_dropdown('ket['.$idx_drop.']', $opt_ket, $att_prsn[$j]->ket2);
                                $drop_ket = $att_prsn[$j]->ket2;
                                foreach ($att_resume as $k) {
                                    if ($k->id == $drop_ket) {
                                        $drop_ket = $k->keterangan;
                                    }
                                }
                            } else {
                                $drop_ket = null;
                            }
                            
                            //echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->waktu_telat . "</td><td class=\"lstc\"".$redText2.">" . ($att_prsn[$j]->is_same ? "TIDAK LENGKAP" : ($att_prsn[$j]->is_late ? "TERLAMBAT" : "")) . "</td></tr>";
                            
                            if (($att_prsn[$j]->waktu_telat == '') || (substr($att_prsn[$j]->waktu_telat,0,5) == "00:00")) {
                                    $durasi_telat = '';
                                } else {
                                    $durasi_telat = substr($att_prsn[$j]->waktu_telat,0,5);
                                }
                            
                            if (isset($drop_ket)) {
                                echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\"".$redText3.">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . $durasi_telat . "</td><td class=\"lstc\"".$redText2.">" . $drop_ket . "</td></tr>";
                            } else {
                                echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\"".$redText3.">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . $durasi_telat . "</td><td class=\"lstc\"".$redText2.">&nbsp;</td></tr>";
                                //echo "<tr><td class=\"lstc\">$compare</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\"".$redText.">" . $att_prsn[$j]->jam_masuk . "</td><td class=\"lstc\">" . $att_prsn[$j]->jam_keluar . "</td><td class=\"lstc\"".$redText.">" . substr($att_prsn[$j]->waktu_telat,0,5) . "</td><td class=\"lstc\"".$redText2.">" . ($att_prsn[$j]->is_same ? "TIDAK LENGKAP" : "") . "</td></tr>";
                            }
                            $drop_ket = "";
                            //if ($att_prsn[$j]->is_late) {
                            if ($durasi_telat != '') {
                                $ttl_waktu_telat = $ttl_waktu_telat+$att_prsn[$j]->sec_waktu_telat;
                                $ttl_telat++; 
                            }
                            
                            if ($att_prsn[$j]->counter) {
                                $ttl_hadir++;
                            }
                             
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
                                //echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">".form_dropdown('ket['.$idx_drop.']', $opt_ket)."</td></tr>";
                                echo "<tr><td class=\"lstc\">$full_date</td><td class=\"lstc\">$txtDay</td><td class=\"lstc\">$libur</td><td class=\"lstc\">$libur</td><td class=\"lstc\">&nbsp;</td><td class=\"lstc\">&nbsp;</td></tr>";
                            }
                        }
                    }
                    echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan=\"2\" class=\"lstc\">Total Durasi Keterlambatan</td><td class=\"lstc\">".mdate("%H:%i", mktime(0, 0, (empty($ttl_waktu_telat)?0:$ttl_waktu_telat), 0, 0, 0))."</td></tr>";
                    echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan=\"2\" class=\"lstc\">Total Keterlambatan (hari)</td><td class=\"lstc\">$ttl_telat</td></tr>";
                    echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td colspan=\"2\" class=\"lstc\">Total Kehadiran (hari)</td><td class=\"lstc\">$ttl_hadir</td></tr>";
                    ?>
                </table>
                <br/>
                <?php
                $jum_kolom = 2;
                $list_summary = "<tr>";
                $count_kolom = 0;
                foreach ($att_resume as $k) {
                    $count_kolom++;
                    $list_summary = $list_summary."<td style=\"padding-left: 5px;\"><b>#</b></td><td style=\"width: 133px; padding-left: 5px;\">$k->keterangan</td><td style=\"width: 30px;\">: $k->jumlah</td>";
                    if ($count_kolom >= $jum_kolom) {
                        $count_kolom = 0;
                        $list_summary = $list_summary."</tr><tr>";
                    }
                }
                $list_summary = $list_summary."</tr>";
                if ($list_summary != "") {
                    echo "<table class=\"notes\">";
                    echo "<tr><td colspan=\"3\" style=\"padding-left: 5px;\"><b>KETERANGAN</b></td></tr>";
                    echo $list_summary;
                    echo "</table>";
                }
                ?>
                <p style="font-size: 4px;"><?= $att_kode ?></p>
                <p><a href="<?= site_url("report/detail_personal_monthly_xls/$att_filter"); ?>">Eksport ke XLS</a></p>
                <p><a href="<?= site_url("report/filter_personal_monthly"); ?>">Kembali</a></p>
                <?= form_close(); ?>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>