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
                <h3>Laporan Presensi Per Karyawan/Dosen Per Bulan</h3>
                <code>
                    <?php
                    $lst_prsnl['ALL'] = 'Semua Karyawan/Dosen';
                    foreach ($prsnl as $key => $value) {
                        $lst_prsnl[$key] = $value;
                    }
                    
                    //print_r($prsnl);
                    echo form_open('report/detail_personal_monthly');
                    echo "<table>";
                    echo "<tr>";
                    echo "<td style='width:110px'>" . form_label('Nama', 'user_id') . "</td>";
                    echo "<td>" . form_dropdown('id', $lst_prsnl) . "</td>";
                    //echo "<td>" . form_dropdown('id', $prsnl) . "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td style='width:110px'>" . form_label('Bulan', 'month') . "</td>";
                    echo "<td>" . form_dropdown('bulan', $bln) . "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td style='width:110px'>" . form_label('Tahun', 'year') . "</td>";
                    echo "<td>" . form_dropdown('tahun', $thn) . "</td>";
                    echo "</tr>";
                    echo "<tr>";
                    echo "<td>&nbsp;</td>";
                    echo "<td>" . form_submit('view', 'Tampilkan') . "</td>";
                    echo "</tr>";
                    echo "</table>";
                    echo form_close();
                    ?>
                </code>
                <p><a href="<?= site_url("report"); ?>">Kembali</a></p>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>