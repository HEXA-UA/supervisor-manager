<?php

use supervisormanager\components\supervisor\Connection;

return [
        'components' => [],
        'params' => [
            'supervisorConnection' => [
                'class' => Connection::class,
                'user' => 'user',
                'password' => '123',
                'url' => 'http://127.0.0.1:9001/RPC2'
            ],
        ],
];