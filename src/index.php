<?php

if (array_key_exists('source', $_GET)) {
  header('Content-Type: text/plain; charset=utf-8');
  echo file_get_contents(__FILE__);
  die();
}

function generateCaptcha($length = 18) {
  $abc = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';

  $word = '';
  for ($i = 0; $i < $length; $i++) {
    $word .= $abc[rand() % strlen($abc)];
  }

  return $word;
}

session_start();
mt_srand(time());

if (array_key_exists('image', $_GET)) {
  $img = imagecreate(200, 40);
  $background = imagecolorallocate($img, 200, 200, 255);
  $text_colour = imagecolorallocate($img, 255, 200, 200);
  $word = generateCaptcha();
  imagestring($img, 8, 20, 12, $word, $text_colour);

  $_SESSION['request_time'] = time();
  $_SESSION['word'] = $word;
  header('Content-Type: image/png');
  imagepng($img);
  die();
}

header('Content-Type: text/html; charset=utf-8');

if (array_key_exists('solution', $_POST)) {
  if (!array_key_exists('request_time', $_SESSION)) {
    die("Failed! request_time");
  }
  if (!array_key_exists('word', $_SESSION)) {
    die('Failed! word');
  }
  if ($_SESSION['request_time'] + 3 < time()) {
    die('Failed! timeout');
  }

  if ($_SESSION['word'] !== $_POST['solution']) {
    die('Failed! wrong word');
  }

  die(file_get_contents('./.htflag'));
}
?>
<html>
  <head>
    <title>Totally Random</title>
  </head>
  <body>
    <div style="text-align: center;">
      <div><img src='/?image&ts=<?php echo time(); ?>'></div>

      <div style='margin-top: 1em; font-weight: bold;'>
        You have 3 seconds to solve this captcha.
        If you can't, then you are not a robot.
      </div>

      <form method="post" style="margin-top: 1em;">
        <div>
          <input type="text" name='solution' placeholder=''>
          <input type="submit" value="Submit">
        </div>
      </form>

      <div>You can check the source code: <a href='/?source'>/index.php?source</a></div>
    </div>
  </body>
</html>
