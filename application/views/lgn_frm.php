<div id="login_form">
    <h3>Attendance</h3>
    <?php
    echo form_open('login/validate_credentials');
    echo form_input('username','','placeholder="Username"');
    echo form_password('password','','placeholder="Password"');
    echo form_submit('login', 'Login');
    if (isset($mssg_error)) {
        echo '<p style="font-size: 11px; color: red; position: absolute; margin-top: -22px; margin-left: 64px;">'.$mssg_error.'</p>';
    }
    //echo anchor('login/signup', 'Create Account');
    ?>
</div>