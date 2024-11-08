<?php

define("RECAPTCHA_SITE_KEY", "");
define("RECAPTCHA_SECRET_KEY", "");

function h($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

if(isset($_POST["g-recaptcha-response"])) {
    $params = [];
    $params["secret"] = RECAPTCHA_SECRET_KEY;
    $params["response"] = $_POST["g-recaptcha-response"];
    if(isset($_SERVER["REMOTE_ADDR"])){
        $params["remoteip"] = $_SERVER["REMOTE_ADDR"]; // remoteipは任意
    }
    $postData = http_build_query($params);

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "Content-Length: " . strlen($postData) . "\r\n",
            'content' => $postData,
        ],
    ];
    $context = stream_context_create($options);
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $response = file_get_contents($url, false, $context);
    if(! $responseData = json_decode($response, true)){
        echo "レスポンスのJSONデータの解析に失敗しました。";
        exit;
    }

    $statusCode = isset($http_response_header[0]) ? explode(' ', $http_response_header[0])[1] : null;
    if($statusCode !== '200'){
        echo "HTTPステータスコードが正常ではありません。".$statusCode;
        exit;
    }

    $html = '<html><body>';
    $html.= '<h1>reCAPTCHA 検証結果</h1>';
    $html.= '<h2>response</h2>';
    $html.= '<p>StatusCode: '.h($statusCode).' '.h($response).'</p>';
    $html.= '<table border="1">';
    foreach($responseData as $key => $value){
        $html.= '<tr><th>' . h($key) . '</th><td>';
        if(is_array($value)){
            foreach($value as $k => $v){
                $html.= h($k) . ': ' . h($v) . '<br>';
            }
        }else{
            $html.= h($value);
        }
        
        $html.= '</td></tr>';
    }
    $html.= '</table>';
    $html.= '<hr>';

    $html.= '<h2>http_response_header</h2>';
    foreach($http_response_header as $header){
        $html.= h($header) . "<br>";
    }
    $html.='</body></html>';

    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}


header('Content-Type: text/html; charset=utf-8');

?><!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>reCAPTCHA 検証ページ</title>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
<h1>reCAPTCHA 検証ページ</h1>

<form id="form" action="<?= $_SERVER["SCRIPT_NAME"] ?>" method="post">
    <div class="g-recaptcha" data-sitekey="<?= RECAPTCHA_SITE_KEY ?>"></div>
    <button type="submit">送信</button>
</form>

  <script>
    document.getElementById('form').addEventListener('submit', function(event) {
      const response = grecaptcha.getResponse();
      if (response.length === 0) {
        event.preventDefault();
        alert('reCAPTCHA を検証してください。');
        return;
      }
    });
  </script>

</body>
</html>


