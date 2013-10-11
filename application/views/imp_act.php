<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>

        <link rel="stylesheet" type="text/css" href="<?= base_url()."files/css/style.css"; ?>">
        
        <script src="<?= base_url()."files/js/jquery.min.js"; ?>"></script>
        <script>
            function buttonClick() {
                $.ajax({
                    type: "POST",
                    data: {mdbfilepath:$('#mdbfile').val()},
                    url: "<?= site_url("/import/mdb_setting/"); ?>",
                    success: function () {
                $('#loading_checkinout').html('<?php echo 'load checkinout <img src="' . base_url() . 'files/image/ajax-loader.gif">'; ?>');
                $.ajax({
                    type: "POST",
                    data: "MDB",
                    url: "<?= site_url("/import/mdb_checkinout"); ?>",
                    success: function () {
                        $('#loading_checkinout').html('sukses checkinout');
                        $('#loading_userinfo').html('<?php echo 'load userinfo <img src="' . base_url() . 'files/image/ajax-loader.gif">'; ?>');
                        $.ajax({
                            type: "POST",
                            data: "MDB",
                            url: "<?= site_url("/import/mdb_userinfo"); ?>",
                            success: function() {
                                $('#loading_userinfo').html('sukses userinfo');
                                $('#loading_departments').html('<?php echo 'load departments <img src="' . base_url() . 'files/image/ajax-loader.gif">'; ?>');
                                $.ajax({
                                    type: "POST",
                                    data: "MDB",
                                    url: "<?= site_url("/import/mdb_departments"); ?>",
                                    success: function() {
                                        $('#loading_departments').html('sukses departments');
                                    }
                                });
                            }
                        });
                    }
                });
             }
             });
             }
        </script>
    </head>
    <body>

        <div id="container">
            <h1>Attendance Information System</h1>

            <div id="body">
                <h3>IMPORT MDB</h3>
                <p><input type="text" name="mdbfile" id="mdbfile" value="<?= $mdbfilepath_local ?>" DISABLED style="width: 400px;"/></p>
                <p><input type="button" name="import" value="Import MDB" onclick="buttonClick()" /></p>

                <div id="loading_checkinout"></div>
                <div id="loading_userinfo"></div>
                <div id="loading_departments"></div>
                <p><a href="#">Kembali</a></p>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>