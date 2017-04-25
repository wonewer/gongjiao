<?php
require __DIR__ . '/vendor/autoload.php';
$client = new \GuzzleHttp\Client(
    array('headers' => array(
        'Cookie' => 'ASP.NET_SessionId=ihp3ev4vsbboaioxl33slgkf'
    )
    )
);
$appKey = '5b54e9c91753aa25dbc6dda0d32975ba';
$appSec = 'f352eb718381';
$nonce = rand(100000, 999999);
$curTime = time();
$checkSum = strtolower(sha1($appSec . $nonce . $curTime));
$sendParam = array('headers' => array(
    'Content-Type' => 'application/x-www-form-urlencoded',
    'charset' => 'utf-8',
    'AppKey' => $appKey,
    'Nonce' => $nonce,
    'CurTime' => $curTime,
    'CheckSum' => $checkSum
)
);
$send = new \GuzzleHttp\Client(
    $sendParam
);
function getData($client, $send)
{
    $res = $client->request('GET', 'http://gongjiao.51ej.cn/PersonalApp/yysd?id=201510161334274687500c9f608f55d&stuid=20151109145814624625039226571da');
    $login = $res->getBody()->getContents();
    $document = FluentDOM::load($login, 'text/html',
        [FluentDOM\Loader\Options::ALLOW_FILE => TRUE]
    );
    foreach ($document('//div[@class=\'time_list_li\']') as $div) {
        $str = trim($div->textContent);
        if (strpos($str,'不可预约') === false) {
            sendMsg($send);
            $div = null;
            unset($div);
            $str = null;
            unset($str);
            exit();
            break;
        }
    }
    $document = null;
    $login = null;
    $res = null;
    unset($document);
    unset($login);
    unset($res);
}

function sendMsg($send)
{
    try {
        $url = 'http://api.netease.im/sms/sendtemplate.action';
        $mobiles = json_encode(array(
            "18512528601"
        ));
        $params = json_encode(array(
            "!!!"
        ));
        $param = array(
            'templateid' => "3059360",
            'mobiles' => $mobiles,
            'params' => $params
        );
        $res = $send->request('POST', $url, array(
            'form_params' =>
                $param
            ));
        echo $res->getBody()->getContents();
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        echo $e->getResponse()->getBody()->getContents();
    }
    return true;
}

while (1) {
    $a = getData($client, $send);
    unset($a);
    sleep(30);
}

function getUsage()
{
    echo memory_get_usage().PHP_EOL;
}
