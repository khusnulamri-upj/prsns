<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>
    </head>
    <body>
        <?php
        echo form_open('att_rpt/dtl_prsn_vw', array('id' => 'next'));
        echo form_hidden('month', $month);
        echo form_hidden('year', $year);
        echo form_hidden('user_id', $user_id);
        form_close();
        ?>
    </body>
</html>
<script>
    document.getElementById("next").submit();
</script>