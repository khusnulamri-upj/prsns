<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>

        <link rel="stylesheet" type="text/css" href="<?= base_url()."files/css/style.css"; ?>">
        
        <script src="<?= base_url()."files/js/jquery.min.js"; ?>"></script>
        <script>
            $( document ).ready(function() {
                $('#loading').html('<?php echo $loading_msg.' <img src="'.base_url().'files/image/ajax-loader.gif">'; ?>');
                $.ajax({
                    type: "POST",
                    data: "ALL",
                    url: "<?= base_url("/thirdparty/summary_department_yearly_xls.php?fltr=".$thn."_ALL"); ?>",
                    success: function () {
                        $('#loading').html('<?php echo $success_msg; ?>');
                        $('#list_files').html('<?php echo $loading2_msg.' <img src="'.base_url().'files/image/ajax-loader.gif">'; ?>');    
                        $.ajax({
                            type: "POST",
                            data: "ALL",
                            url: "<?= site_url("report/summary_department_yearly_all_files"); ?>",
                            success: function (r) {
                                $('#loading').html('');
                                $('#list_files').html(r);
                            }
                        });
                    }
                });
            });
        </script>
    </head>
    <body>
        <?php $this->load->view('includes/topinfo'); ?>
        <div id="container">
            <h1>Attendance Information System</h1>

            <div id="body">
                <h3>Laporan Kedatangan dan Kepulangan Semua Prodi/Bagian</h3>
                <div id="loading"></div>
                <div id="list_files"></div>
                <p><a href="<?= site_url("report/filter_department_yearly"); ?>">Kembali</a></p>
            </div>
            
            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
        
        
    </body>
</html>