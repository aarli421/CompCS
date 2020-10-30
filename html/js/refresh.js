var time = 120000;
setInterval(function(){$.post('https://www.compcs.codes/ajax/refresh', function (data) {
        console.log(data);
    });
}
,time);