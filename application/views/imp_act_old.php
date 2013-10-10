<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>

        <link rel="stylesheet" type="text/css" href="<?= base_url()."files/css/style.css"; ?>">
        
        <script src="<?= base_url()."files/js/jquery.min.js"; ?>"></script>
        <script src="<?= base_url()."files/js/custom_ajaxSeq.js"; ?>"></script>
        <script>
                       
            function importMdb() {
                $("#loading-status").show();
                var controllers = ['../import/mdb_checkinout', '../import/mdb_departments', '../import/mdb_userinfo'];
                sequenceRequest('../import/setting', controllers);
                //$("#loading-status").hide();
            }
            function buttonClick() {
                importMdb()
                $("#loading-status").hide();
            }
        </script>
    </head>
    <body>
        <div id="container">
            <h1>Attendance Information System</h1>

            <div id="body">
                <p>IMPORT</p>
                <input type="button" onclick="buttonClick();">
                <p id="track-record">&nbsp;</p>
                <p id="loading-status"></p>
                <p><a href="#">Kembali</a></p>
            </div>
            
            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
        
        
    </body>
</html>