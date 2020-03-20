<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    p.php
 * Created: 2020/3/20 09:15:32
 */
declare(strict_types=1);
require_once __DIR__."/../vendor/autoload.php";

use YTOSdk\YTO;

$conf = [
    'customer_code' => 'yto_user',
    'verify_code' => '123456',
    'app_key' => 'ABCDEF',
    'v' => '1.01'
];
$p = new YTO($conf);
$result = false;
//$result =$p->quickOrderCancel('LP07082300225709', ['remark'=> '测试']);
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
//$result =$p->quickOrderGeneral($order);
//$result =$p->quickOrderElectronic($order);

$data = [
    'start_province' => '甘肃省',
    'start_city' => '金昌市',
    'end_province' => '湖南省',
    'end_city' => '湘西土家族苗族自治州',
    'goods_weight' => 0.5,
    'goods_length' => '',
    'goods_width' => '',
    'goods_height' => ''
];
$result = $p->queryTransportPrice($data);
var_dump($result);