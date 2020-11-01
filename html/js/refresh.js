var time = 300000;
// var time = 60000;
window.setInterval( function() {
    $.ajax({
        cache: false,
        type: "GET",
        url: "https://www.compcs.codes/ajax/refresh.php",
        success: function(data) {
            console.log(data);
        }
    });
}, time );