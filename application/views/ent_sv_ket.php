<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>
    </head>
    <body>
        <?php
        echo form_open('entry/view', array('id' => 'next'));
        echo form_hidden('bulan', $month);
        echo form_hidden('tahun', $year);
        echo form_hidden('id', $user_id);
        form_close();
        ?>
    </body>
</html>
<script>
    document.getElementById("next").submit();
</script>