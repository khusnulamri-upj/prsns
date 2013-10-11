    //// upload_form.html
    <html>
    <body>
    <form enctype="multipart/form-data" action="http://localhost/ci_attendance/files/newEmptyPHP.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
    Choose a file to upload: <input name="uploadedfile" type="file" /><br />
    <input type="submit" value="Upload File" />
    </form>
    </body>
    </html>
    ///////upload_file.php
    <?php
    $ftp_server = "192.168.60.154";
    $ftp_username = "amri";
    $ftp_password = "amri123";
    //setup of connection
    $conn_id = ftp_connect($ftp_server) or die("could not connect to $ftp_server");
    //login
    if(@ftp_login($conn_id, $ftp_username, $ftp_password))
    {
    echo "conectd as $ftp_username@$ftp_server\n";
    }
    else {
    echo "could not connect as $ftp_username\n";
    }
    $file = $_FILES["file"]["name"];
    $remote_file_path = "/files/mdb/".$file;
    ftp_put($conn_id, $remote_file_path, $file, FTP_ASCII);
    ftp_close($conn_id);
    echo "\n\nconnection closed";