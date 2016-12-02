<?php

define('VERSION', 'v1');
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'local'));

return new \Phalcon\Config(
    [
        /**
         * The name of the database
         */
        'database'  => [
            'mysql'     => [
                'host'     => 'localhost',
                'username' => 'root',
                'password' => '',
                'dbname'   => 'app',
                'charset'  => 'utf8',
            ]
        ],
        /**
         * Application settings
         */
        'app' => [

            //The site name, you should change it to your name website
            'name'  => 'Lackky',
            'debug' => true
        ],

        'vision' => [
            'secretKey' => 'xxx-xxx-xxx-xxx-xxx',
            'baseUrl' => 'https://api.projectoxford.ai/vision/v1.0/'
        ]
    ]
);
