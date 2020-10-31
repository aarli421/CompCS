var time = 120000;
setInterval(function(){$.post('https://www.compcs.codes/ajax/loggedin.php', function (data) {
    if (data == "Not logged in") {
        $(location).attr("href", "https://www.compcs.codes/login");
    }
});
}
,time);