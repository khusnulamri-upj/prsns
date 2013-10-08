<h1>Create Account</h1>
<fieldset>
    <legend>Information</legend>
    <?php
    echo form_open('login/create_member');
    echo form_input('email_address','','placeholder="Email Address"');
    echo form_input('username','','placeholder="Username"');
    echo form_password('password','','placeholder="Password"');
    echo form_password('passwordcon','','placeholder="Password Confirmation"');
    echo form_submit('create', 'Create Account');
    echo validation_errors('<p class="error">');
    ?>
</fieldset>

