<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>
        
        <link rel="stylesheet" type="text/css" href="<?= base_url()."files/css/style.css"; ?>">
        
    </head>
    <body>
        <div id="container">
            <h1>Attendance Information System</h1>

            <div id="body">
                <h3>Laporan Presensi Personil</h3>
                <code>
                    <?php
                    echo form_open('report/summary_department_yearly_xls');
                    echo "<table>";
                    echo "<tr>";
                    echo "<td style='width:110px'>" . form_label('Prodi/Bagian', 'dept_id') . "</td>";
                    echo "<td>" . form_dropdown('id', $dept) . "</td>";
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