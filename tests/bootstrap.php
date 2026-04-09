<?php

require __DIR__.'/../vendor/autoload.php';

$appKey = getenv('APP_KEY');

if (!$appKey) {
    $appKey = 'base64:'.base64_encode(random_bytes(32));

    putenv('APP_KEY='.$appKey);
    $_ENV['APP_KEY'] = $appKey;
    $_SERVER['APP_KEY'] = $appKey;
}
