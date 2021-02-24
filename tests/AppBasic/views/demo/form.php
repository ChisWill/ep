<h2>Form</h2>

<p>
    <a href="form">新纪录</a>
</p>
<p>
    <a href="redirect">编辑最新的记录</a>
</p>

<form method="post" id="form">
    账号：
    <input type="text" name="User[username]" value="<?= $user->username ?>">
    密码：
    <input type="text" name="User[password]" value="<?= $user->password ?>">
    年龄：
    <input type="text" name="User[age]" value="<?= $user->age ?>">
    出生年月：
    <input type="text" name="User[birthday]" value="<?= $user->birthday ?>">

    <input type="submit" id="btn">
</form>
<script src="https://lib.baomitu.com/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(function() {
        $("#btn").click(function() {
            $.post('', $("#form").serialize(), function(r) {
                if (r.errno == 0) {
                    alert('ok');
                    location.reload();
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