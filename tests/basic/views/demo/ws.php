<script src="https://lib.baomitu.com/jquery/3.5.1/jquery.min.js"></script>

<h1>Swoole WebSocket Server</h1>

<div>
    <input type="text" id="text">

    <input type="button" id="button" value="提交">
</div>

<h3>消息区域</h3>
<div id="area">

</div>

<script>
    var wsServer = 'ws://127.0.0.1:9502/websocket';
    var websocket = new WebSocket(wsServer);

    var display = function(data, type = 2) {
        var date = new Date;
        var msg = data + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();

        if (type == 1) {
            msg = '发送：' + msg;
        } else {
            msg = '接收：' + msg;
        }

        $msg = $("<p>").html(msg);
        $("#area").append($msg);
    }


    $("#button").click(function() {
        var data = $("#text").val();
        display(data, 1);
        websocket.send(data);
    });

    websocket.onopen = function(evt) {
        display('Connected to WebSocket server.');
        websocket.send('hello');
    };

    websocket.onclose = function(evt) {
        display("Disconnected");
    };

    websocket.onmessage = function(evt) {
        display(evt.data);
    };

    websocket.onerror = function(evt, e) {
        display('Error occured: ' + evt.data);
    };
</script>