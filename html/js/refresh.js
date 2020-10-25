var time = 300000; // 5 mins
setInterval(
    function () {
        $.ajax({
            url: 'ajax/refresh.php',
            cache: false
        });
    },
    time
);