<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'panels' => [
//            'mongodb' => [
//                'class' => 'yii\mongodb\debug\MongoDbPanel',
//                // 'db' => 'mongodb', // MongoDB component ID, defaults to `db`. Uncomment and change this line, if you registered MongoDB component with a different ID.
//            ],
            'httpclient' => [
                'class' => 'yii\httpclient\debug\HttpClientPanel',
            ],
            'queue' => [
                'class' => 'yii\queue\debug\Panel'
            ],
        ],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '192.168.10.111'],
        'generators' => [
            'crud' => [ //生成器名称
                'class' => 'yii\gii\generators\crud\Generator',
                'template' => 'Inspinia',
                'templates' => [
                    //模板名 => 模板路径
                    'Inspinia' => '@vendor/xutl/yii2-inspinia-widget/gii/generators/crud',
                    'YUNCMS' => '@frontend/views/gii/crud',
                ]
            ],
            'model' => [ //生成器名称
                'class' => 'yii\gii\generators\model\Generator',
                'template' => 'Inspinia',
                'templates' => [
                    //模板名 => 模板路径
                    'Inspinia' => '@vendor/xutl/yii2-inspinia-widget/gii/generators/model',
                    'YUNCMS' => '@frontend/views/gii/model',
                ]
            ],
            'module' => [ //生成器名称
                'class' => 'yii\gii\generators\module\Generator',
                'template' => 'Inspinia',
                'templates' => [
                    //模板名 => 模板路径
                    'Inspinia' => '@vendor/xutl/yii2-inspinia-widget/gii/generators/module',
                    'YUNCMS' => '@frontend/views/gii/module',
                ]
            ],
            'controller' => [ //生成器名称
                'class' => 'yii\gii\generators\controller\Generator',
                'template' => 'Inspinia',
                'templates' => [
                    //模板名 => 模板路径
                    'Inspinia' => '@vendor/xutl/yii2-inspinia-widget/gii/generators/module',
                    'YUNCMS' => '@frontend/views/gii/controller',
                ]
            ],
            'extension' => [ //生成器名称
                'class' => 'yii\gii\generators\extension\Generator',
                'template' => 'Inspinia',
                'templates' => [
                    //模板名 => 模板路径
                    'Inspinia' => '@vendor/xutl/yii2-inspinia-widget/gii/generators/extension',
                    'YUNCMS' => '@frontend/views/gii/extension',
                ]
            ],
            'form' => [ //生成器名称
                'class' => 'yii\gii\generators\form\Generator',
                'template' => 'Inspinia',
                'templates' => [ //设置我们自己的模板
                    //模板名 => 模板路径
                    'Inspinia' => '@vendor/xutl/yii2-inspinia-widget/gii/generators/form',
                    'YUNCMS' => '@frontend/views/gii/form',
                ]
            ],
//            'sphinxModel' => [
//                'class' => 'yii\sphinx\gii\model\Generator',
//                'templates' => [
//                    'YUNCMS' => '@frontend/views/gii/sphinx',
//                ]
//            ],
//            'mongoDbModel' => [
//                'class' => 'yii\mongodb\gii\model\Generator',
//                'templates' => [
//                    'YUNCMS' => '@frontend/views/gii/mongo',
//                ]
//            ],
            'queue' => [
                'class' => 'yii\queue\gii\Generator',
                'templates' => [
                    'YUNCMS' => '@frontend/views/gii/queue',
                ]
            ],
        ]
    ];
}

return $config;
