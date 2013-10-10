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
                <code>
                    <p>+ <a href="<?= site_url("entry"); ?>">Input Presensi Karyawan/Dosen</a></p>
                    <p>+ <a href="<?= site_url("report"); ?>">Laporan Presensi Karyawan/Dosen</a></p>
                    <p>+ <a href="<?= site_url("import"); ?>">Import Database</a></p>
                </code>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>