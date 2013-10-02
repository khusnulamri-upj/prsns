<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Attendance Information System</title>

        <link rel="stylesheet" type="text/css" href="<?= base_url()."files/css/style.css"; ?>">
        
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>
            function fireRequest(url, data) {
                return $.ajax({
                    type: 'POST',
                    url: url/*,
                    data: JSON.stringify(data),
                    dataType: 'json'*/
                });
            }
            var data = [{one: 1}, {two: 2}],
                    types = ['../import/mdb_checkinout', '../import/mdb'];
            console.log('first1is');
            function
            startingpoint = fireRequest('../import/setting', {zero: 0});
            $.each(types, function(ix, type) {
                startingpoint = startingpoint.pipe(function(response, status, jqXhr) {
                    // This should only get called 2 times
                    console.log('firstis');
                    console.log('The status is ' + status + response);
                    return fireRequest(type, data[ix]);
                },
                function(jqXhr, status, httpResponse) {
                    // This will get called once
                    console.log('The status is ' + status + ' with response ' + httpResponse);
                    console.log('lastisis');
                });
                // The last call after /some/non-existant/url will never fire
                // To see this, open up firebug or chrome dev tools and check the network tab
                // for the requests
            });
        </script>
    </head>
    <body>

        <div id="container">
            <h1>Attendance Information System</h1>

            <div id="body">
                <p>IMPORT</p>

                <p><a href="#">Kembali</a></p>
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
        </div>
    </body>
</html>