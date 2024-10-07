<?php 

return [
    'niceLinks' => true,
    'routes' => require_once 'routes.php',
    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'dbname' => 'test'
        ],
        'test2' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'dbname' => 'test2'
        ]
    ],
];