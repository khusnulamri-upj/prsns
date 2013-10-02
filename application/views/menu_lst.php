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
                <code>
                    <p>+ <a href="<?= site_url("entry"); ?>">Input Presensi Personil</a></p>
                    <p>+ <a href="<?= site_url("report"); ?>">Laporan Presensi Personil</a></p>
                </code>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>