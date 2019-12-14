function GetURLParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) {
            return sParameterName[1];
        }
    }
}

var kiosk = GetURLParameter('kiosk');

if (kiosk) {
    const options = { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric'};

    $("div.container div.events.index").prepend("<div id='kiosk-clock' style='float: left;font-size: 30px;'></div>");

    jQuery(function($) {
        setInterval(function() {
            var time = new Date();
            $("#kiosk-clock").html(time.toLocaleDateString('en-US', options));
        }, 1000);
    });
}
