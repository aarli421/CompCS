// var time = 300000;
var time = 60000;
setInterval(function(){$.post('https://www.compcs.codes/ajax/refresh.php', function (data) {
        console.log(data);
    });
}
,time);