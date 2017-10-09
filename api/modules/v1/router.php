<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
return [
    //第一版
    [//公共接口
        'class' => 'yii\rest\UrlRule',
        'except' => ['delete', 'create', 'update'],
        'controller' => ['v1/category', 'v1/language', 'v1/area', 'v1/country', 'v1/currency']
    ],
    [//话题
        'class' => 'yii\rest\UrlRule',
        'except' => ['delete', 'create', 'update'],
        'controller' => 'v1/topic',
        'extraPatterns' => [
            'GET search' => 'search',
        ],
    ],
    [//文章
        'class' => 'yii\rest\UrlRule',
        'controller' => ['v1/article'],
        'extraPatterns' => [
            'POST <id:\d+>/collection' => 'collection',
            'POST <id:\d+>/support' => 'support',
        ],
    ],
    [//问答
        'class' => 'yii\rest\UrlRule',
        'controller' => ['v1/question'],
        'extraPatterns' => [
            'POST <id:\d+>/collection' => 'collection',
            'GET,POST <id:\d+>/answers' => 'answer',
        ],
    ],
    [//用户
        'class' => 'yii\rest\UrlRule',
        'controller' => 'v1/user',
        'except' => ['delete', 'create'],
        'extraPatterns' => [
            'GET search' => 'search',
            'POST avatar' => 'avatar',
            'GET post' => 'register',
        ],
//        'ruleConfig' => [//额外的包含规则
//            'class' => 'yii\web\UrlRule',
//            'defaults' => [
//                'expand' => 'profile',
//            ]
//        ],
    ],
    [//支付
        'class' => 'yii\rest\UrlRule',
        //'except' => ['delete', 'create', 'update','view'],
        'controller' => 'v1/payment',
        'extraPatterns' => [
            'GET search' => 'search',
        ],
    ],
];