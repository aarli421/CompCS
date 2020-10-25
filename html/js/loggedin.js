var time = 60000; // 1 min
setInterval(
    function () {
        $.ajax({
            url: 'ajax/loggedin.php',
            cache: false,
            success: function(data) {
                if (data == "Not logged in") {
                    $(location).attr("href", "https://www.compcs.codes/login");
                }
            }
        });
    },
    time
);