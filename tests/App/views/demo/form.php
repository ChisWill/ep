<h2>Form</h2>

<form method="post" id="form">
    账号：
    <input type="text" name="username">
    密码：
    <input type="text" name="password">
    年龄：
    <input type="text" name="age">
    标题：
    <input type="text" name="title">

    <input type="submit" id="btn">
</form>
<script>
    $(function() {
        $("#btn").click(function() {
            $.post('', $("#form").serialize(), function(r) {
                if (r.errno == 0) {
                    var s = '';
                    var d = '';
                    for (var k in r.body) {
                        s += d + r.body[k];
                        d = "\n";
                    }
                    alert(s);
                } else {
                    var s = '';
                    var d = '';
                    for (var k in r.error) {
                        s += d + k + '：' + r.error[k];
                        d = "\n";
                    }
                    alert(s);
                }
            }, 'json');
            return false;
        });
    });
</script>