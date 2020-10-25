var time = 6; // 10 mins
setInterval(
    function () {
        $.ajax({
            url: 'ajax/refresh.php',
            cache: false
        });
    },
    time
);