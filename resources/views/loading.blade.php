<!DOCTYPE html>
<html lang="ja">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</head>
<body>
<?php
use Illuminate\Support\Facades\Log;
Log::debug('LOADING');
?>

<script>
    var url_query = location.search
    //OAuthでエラーがあった場合はログイン画面に遷移
    var error = url_query.indexOf('error=');
    if(error >= 0){
        console.log('AOuth error');
        console.log(error);
        alert(error);
        window.location.href = 'login.php';
    //エラーがない場合はAccessToken取得に向けてPOST
    }else{
        console.log('loading');
        var verify_start_index = url_query.indexOf('state=') + 'state='.length;
        var verify_end_index = url_query.indexOf('&code=');
        var verify_str = url_query.substring(verify_start_index, verify_end_index);
        var code_index = url_query.indexOf('code=') + 'code='.length;
        var code_str = url_query.substring(code_index);

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'http://localhost/twitter-register';
        
        var request_verify = document.createElement('input');
        request_verify.type = 'hidden'; 
        request_verify.name = 'verify';
        request_verify.value = verify_str;
        form.appendChild(request_verify);

        var request_code = document.createElement('input');
        request_code.type = 'hidden'; 
        request_code.name = 'code';
        request_code.value = code_str;
        form.appendChild(request_code);

        document.body.appendChild(form);

        var csrf = document.createElement('input');
        // すでに存在しているname="csrf-token"のvalueの値を取得する。
        // var token = document.getElementById('csrf-token').value;
        
        console.log(token);
        csrf.type = 'hidden';
        csrf.name = 'csrf-token';
        csrf.value = token;
        form.appendChild(csrf);
　
        console.log(url_query);
        //console.log(form.submit());
        
        

        var token = document.getElementsByName('csrf-token').item(0).content;

        function testRequest(verify, code){
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState==4 && this.status==200) {
                location.href = '/home';
                }
            };
            var token = document.getElementsByName('csrf-token').item(0).content; // 追加
            xhr.responseType = 'json';
            xhr.open('POST', 'http://localhost/twitter-register', true);
            xhr.setRequestHeader('X-CSRF-Token', token); // 追加
            xhr.setRequestHeader("Content-Type", "application/json");
            request_data = JSON.stringify({code_verifier: verify, code: code});
            xhr.send(request_data);
        }
        testRequest(verify_str, code_str);

    }
</script>
</body>
</html>