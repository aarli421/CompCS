var time = 300000;
// var time = 60000;
window.setInterval( function() {
    $.ajax({
        cache: false,
        type: "GET",
        url: "https://www.compcs.org/ajax/loggedin.php",
        success: function(data) {
            if (data == "Not logged in") {
                $(location).attr("href", "https://www.compcs.org/login");
            }
        }
    });
}, time );