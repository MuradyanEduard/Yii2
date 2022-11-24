<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'auth',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'container' => [
        'definitions' => [
            \yii\widgets\LinkPager::class => \yii\bootstrap5\LinkPager::class,
        ],
    ],
    'components' => [

        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '6FlzESRtRHQLujfSAA8G60KoAPqNAHn4',

//            'parsers' => [
//                'application/json' => 'yii\web\JsonParser'
//            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            //ste inch petqa poxel??
            'errorAction' => 'auth/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,

            'rules' => [
//                ['class' => 'yii\rest\UrlRule','controller' => 'author'],
//                ['class' => 'yii\rest\UrlRule','controller' => 'book'],
                '/' => 'auth/login',
                '/auth/logout' => 'auth/logout',
                '/signup' => 'auth/signup',
                '/book' => 'book/index',
                '/book/create' => 'book/create',
                '/book/view' => 'book/view',
                '/author' => 'author/index',
                '/author/create' => 'author/create',
                '/author/view' => 'author/view',
                '/order' => 'order-view/index',
                '/order/admin' => 'order/admin',
                '/order/customer' => 'order/customer',
                '/order/user' => 'order/user',
                '/book/order/add' => 'order/add-product',
                '/book/order/remove' => 'order/add-remove',
                '/book/order/create' => 'order/create',
            ],
        ],

    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
