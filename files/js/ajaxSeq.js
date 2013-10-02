function fireRequest(url) {
    return $.ajax({
        type: 'POST',
        url: url
    });
}

function sequenceRequest(initial, urls) {
    console.log('Sequence Start');
    startingpoint = fireRequest(initial);
    $.each(urls, function(ix, urlx) {
        startingpoint = startingpoint.pipe(function(response, status, jqXhr) {
            console.log('Sequence ' + ix + ' is ' + status);
            return fireRequest(urlx);
        },
                function(jqXhr, status, httpResponse) {
                    // This will get called once
                    console.log('Sequence ' + ix + ' is ' + status + ' with response ' + httpResponse);
                });
    });
    console.log('Sequence Finish');
}