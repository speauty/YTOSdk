<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    simple.php
 * Created: 2020/3/18 16:54:05
 */
declare(strict_types=1);
require_once __DIR__."/../vendor/autoload.php";

use YTOSdk\YTO;

$conf = [
    'customer_code' => 'K21000119',
    'partner_id' => 'u2Z1F7Fh',
    'path' => '/service/e_order_create/v1/oksM7N',
    'type' => 'offline',
    'is_electronic' => true
];
$yto = new YTO($conf);
$result = null;

/*$dataOrderCancel = [
    'UpdateInfo' => [
        'logisticProviderID' => 'YTO',
        'clientID' => 'TEST',
        'txLogisticID' => 'LP07082300225709',
        'infoType' => 'INSTRUCTION',
        'infoContent' => 'WITHDRAW',
        'remark' => '1'
    ]
];*/
// 'path' => '/service/order_cancel/v1/oksM7N',
//$result =$yto->quickOrderCancel('LP07082300225709', ['remark' => 'test']);
$order = [
    'order_no' => 'T000001',
    'sender' => [
        'name' => '张三',
        'post_code' => '0',
        'phone' => '13575745195',
        'prov' => '上海',
        'city' => '上海,浦东区',
        'address' => '新龙科技大厦9层',
    ],
    'receiver' => [
        'name' => '李四',
        'post_code' => '0',
        'phone' => '13575745195',
        'prov' => '上海',
        'city' => '上海,浦东区',
        'address' => '新龙科技大厦9层',
    ],
    'products' => [
        [
            'item_name' => 'FOMOMY Cameo Pink 日抛 10片装',
            'number' => 1,
            'item_value' => 150,
        ],
        [
            'item_name' => 'FOMOMY Cameo Pink 年抛 5片装',
            'number' => 3,
            'item_value' => 13,
        ]
    ],
];
// 'path' => '/service/order_create/v1/oksM7N',
//$result = $yto->quickOrderGeneral($order);
// 'path' => '/service/e_order_create/v1/oksM7N',
//$result = $yto->quickOrderElectronic($order);
// YT2990465807644 YT2990465807644
var_dump($result);