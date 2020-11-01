var time = 600000;
setInterval(function(){$.post('https://www.compcs.codes/ajax/refresh.php', function (data) {
        console.log(data);
    });
}
,time);