<?php

require __DIR__ . '/vendor/autoload.php';

$client = new \GuzzleHttp\Client([
    'base_url' => 'http://localhost:8000',
    'defaults' => [
        'exceptions' => false
    ]
]);
$nickname = 'ObjectOrienter ' . mt_rand(0, 999);
$data = [
    'nickname' => $nickname,
    'avatarNumber' => 3,
    'tagLine' => 'a test dev!'
];

$response = $client->post('/api/programmers', [
    'body' => json_encode($data)
]);
echo $response;
echo "\n\n";
