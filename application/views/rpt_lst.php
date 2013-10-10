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
                <h3>Laporan Presensi Karyawan/Dosen</h3>
                <code>
                    <p>+ <a href="<?= site_url("report/filter_personal_monthly"); ?>">Laporan Presensi Per Karyawan/Dosen Per Bulan</a></p>
                    <p>+ <a href="<?= site_url("report/filter_department_yearly"); ?>">Laporan Presensi Per Prodi/Bagian Per Tahun</a></p>
                </code>
                <p><a href="<?= site_url("menu"); ?>">Kembali</a></p>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>