var time = 60000; // 1 min
setInterval(
    function () {
        $.ajax({
            url: 'ajax/refresh.php',
            cache: false
        });
    },
    time
);