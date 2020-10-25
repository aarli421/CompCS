var time = 600000; // 10 mins
setInterval(
    function () {
        $.ajax({
            url: 'ajax/refresh.php',
            cache: false
        });
    },
    time
);