<?php

use \Dominik\Stellar\Federation\Server;

include __DIR__ . '/../vendor/autoload.php';

$resolver = [
    'example.org' => [
        'user' => 'gDnu3fdGNNAuUy84DmbfyxwELjfu8kpmHg',
    ],
];

$server = new Server($resolver);
$server->exec();
