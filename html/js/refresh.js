var refreshSession = function () {
    var time = 600000; // 10 mins
    setTimeout(
        function () {
            $.ajax({
                url: 'ajax/refresh.php',
                cache: false,
                complete: function () {
                    refreshSession();
                }
            });
        },
        time
    );
};