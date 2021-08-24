var time = 900000;
// var time = 60000;
window.setInterval( function() {
    $.ajax({
        cache: false,
        type: "GET",
        url: "https://www.compcs.org/ajax/refresh.php",
        success: function(data) {
        }
    });
}, time );