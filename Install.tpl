<!DOCTYPE html>
<html>
<head>
    <title>Fraym installation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="/css/install/install.css" media="all">
</head>
<body id="install">
<div id="wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="text-center">
                <img class="logo img-responsive" src="data:image/png;base64,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" />
            </div>
            <div id="spinner">
            </div>
            <div id="text" class="text-center">Please wait, downloading composer...</div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/spin.js/2.3.2/spin.min.js" type="text/javascript"></script>
<script>
    var opts = {
        lines: 7 // The number of lines to draw
        , length: 0 // The length of each line
        , width: 14 // The line thickness
        , radius: 11 // The radius of the inner circle
        , scale: 1 // Scales overall size of the spinner
        , corners: 1 // Corner roundness (0..1)
        , color: '#fff' // #rgb or #rrggbb or array of colors
        , opacity: 0.25 // Opacity of the lines
        , rotate: 0 // The rotation offset
        , direction: 1 // 1: clockwise, -1: counterclockwise
        , speed: 1 // Rounds per second
        , trail: 57 // Afterglow percentage
        , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
        , zIndex: 2e9 // The z-index (defaults to 2000000000)
        , className: 'spinner' // The CSS class to assign to the spinner
        , top: '35px' // Top position relative to parent
        , left: '50%' // Left position relative to parent
        , shadow: false // Whether to render a shadow
        , hwaccel: false // Whether to use hardware acceleration
        , position: 'relative' // Element positioning
    }
    var target = document.getElementById('spinner');
    var spinner = new Spinner(opts).spin(target);

    var load = function() {
        $.ajax({
            url: '',
            dataType:'json',
            type:'post',
            success:function (data, textStatus, jqXHR) {
                if(data.error === false) {
                    $('#text').html(data.message);
                    if(data.done) {
                        load();
                    } else {
                        window.location.reload();
                    }
                } else {
                    spinner.stop();
                    $('#text').html("<h1>Error! :-(</h1>" + data.error);
                }
            },
            error: function(data){
                $('#text').html("<h1>Error! :-(</h1>" + data.responseText);
            }
        });
    };
    load();
</script>
</body>
</html>