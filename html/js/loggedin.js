var time = 300000;
// var time = 60000;
setInterval(function(){$.get('https://www.compcs.codes/ajax/loggedin.php', function (data) {
    if (data == "Not logged in") {
        $(location).attr("href", "https://www.compcs.codes/login");
    }
});
}
,time);