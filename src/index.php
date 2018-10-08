<?php

define('TIMEOUT', 2);

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
  $word = generateCaptcha();
  $font = [
    'size'  => 24,
    'angle' => 0,
    'face'  => '/usr/share/fonts/truetype/anonymous-pro/Anonymous Pro.ttf',
  ];
  $dimensions = imagettfbbox($font['size'], $font['angle'], $font['face'], $word);
  $imageData = [
    'width'  => $dimensions[2] + 40,
    'height' => $dimensions[3] + 40,
  ];
  $img = imagecreate($imageData['width'], $imageData['height']);
  $background = imagecolorallocate($img, 200, 200, 255);
  $text_color = imagecolorallocate($img, 200, 180, 200);

  imagettftext(
    $img,
    $font['size'], $font['angle'],
    20, $imageData['height'] / 2 + 10,
    $text_color,
    '/usr/share/fonts/truetype/anonymous-pro/Anonymous Pro.ttf',
    $word
  );
  # imagestring($img, 8, 20, 12, $word, $text_color);

  for ($i = 2; $i < $imageData['height']; $i += 2) {
    imageline($img, 0, $i, $imageData['width'], $i, $background);
  }
  for ($i = 5; $i < $imageData['width']; $i += 5) {
    imageline($img, $i, 0, $i, $imageData['height'], $background);
  }

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
  if ($_SESSION['request_time'] + TIMEOUT < time()) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Totally Random</title>
  </head>
  <body>
    <div class="container">
      <h1 class="header center orange-text">Prove you are a robot!</h1>
      <div class="row">

        <div style='margin-top: 1em; font-weight: bold;' class="center">
          You have <?php echo TIMEOUT ?> seconds to solve this captcha.
          If you can't, then you are not a robot.
        </div>

        <form class="col offset-s3 s6" target="" data-method="post">
          <div class="col s12 center" style="margin-bottom: 2em; margin-top: 2em;">
            <img src='/?image&ts=<?php echo time(); ?>'>
          </div>
          <div class="input-field col s12">
            <input placeholder="Solution" name="solution" id="register_solution" type="text">
            <label for="register_solution">Solution</label>
          </div>
          <div class="input-field col s12 center">
            <input class="btn" type="button" value="Submit">
          </div>
        </form>

        <div class='center col s12'>
          You can check the source code: <a href='/?source'>/index.php?source</a>
        </div>
      </div>
    </div>
  </body>
</html>
