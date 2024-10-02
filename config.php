<?php 

return [
    'routes' => require_once 'routes.php',
    'db' => [
        'somedb' => [
            'type' => 'mysql:host',
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'dbname' => 'test'
        ]
    ],
];