<!DOCTYPE html>
<html>
<head>
    <title>Fraym installation</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <link rel="stylesheet" type="text/css" href="/css/fraym/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="/css/install/install.css" media="all">
</head>
<body id="install">
<div id="wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="text-center">
                <img src="/images/fraym/logo-white.png" class="logo img-responsive" />
            </div>
            <div id="spinner">
            </div>
            <div id="text" class="text-center">Please wait, downloading composer...</div>
        </div>
    </div>
</div>
<script src="/js/fraym/libs/jquery.min.js" type="text/javascript"></script>
<script src="/js/fraym/libs/spin.min.js" type="text/javascript"></script>
<script>
    var opts = {
        lines: 7 // The number of lines to draw
        , length: 0 // The length of each line
        , width: 14 // The line thickness
        , radius: 11 // The radius of the inner circle
        , scale: 0.75 // Scales overall size of the spinner
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
        , top: '7%' // Top position relative to parent
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
                $('#text').html(data.message);
                if(data.done) {
                    load();
                } else {
                    window.location.reload();
                }
            }
        });
    };
    load();
</script>
</body>
</html>