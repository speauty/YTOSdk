#### 圆通快递PHP-SDK

害, 太能折腾了, 刚写完顺丰, 转过来就要对接圆通, 真想用快递鸟等集成接口. 当然, 为了方便以后使用, 我就把这些都整理成了包, 要用的时候, 直接安装就行了. 自然, 有问题, 修改再发布, 也还是可以的. 

* 基础说明

搞不懂圆通那么多同值的字段是为啥, 一会儿客户编码, 一会儿渠道编码, 一会儿客户标识. 还有就是校验码当 `partnerID` 在用, 唬我不知道`abcd`么. 众多不同的名称实在令我有些头昏眼花, 希望以后圆通那边的技术能统一一下名称什么的吧.

现阶段, 主要对接了三个接口, 献上源码
```php
<?php
declare(strict_types=1);
require_once __DIR__."/../vendor/autoload.php";

use YTOSdk\YTO;
// customer_code和partner_id好像有三对, 看着像是写死的
// 订单服务类 TEST 123456
// 查询服务类 YTOTEST 1QLlIZ
// 电子面单类 K21000119 u2Z1F7Fh
$conf = [
    // 客户编码/渠道编码/客户标识 好像都是这个
    'customer_code' => 'TEST',
    // 合作伙伴标识
    'partner_id' => '123456',
    // uri地址
    // 订单创建 /service/order_create/v1/oksM7N
    // 订单取消 service/order_cancel/v1/oksM7N
    // 走件流程查询 /service/waybill_query/v1/oksM7N
    // 电子面单下单 /service/e_order_create/v1/oksM7N
    'base_uri' => 'http://opentestapi.yto.net.cn',
    // 请求地址路径
    'path' => '/service/order_cancel/v1/oksM7N',
    // 订单类型[online:在线下单，offline:线下下单] 这个需要补充一下, 电子面单那里必须为线下
    'type' => 'online',
    // 是否为电子面单, 默认为false, 主要控制错误提示的输出
    'is_electronic' => false
];
// 然后就是获得一个实例, 下面要对接具体接口功能
$yto = new YTO($conf);
```

[官方文档参考地址](http://open.yto.net.cn/interfaceDocument/menu249)

* 订单服务类
   * 订单创建接口[已接通]
   ```php
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
  // extData支持的数据 'mailNo' ,'totalServiceFee' 'codSplitFee' 'orderType' 'serviceType' 'flag' 'special' 'remark'
  $extData = [];
   $result =$yto->quickOrderGeneral($order, $extData);
   // 返回的数据
   /**
    array(3) {
      ["state"]=>
      bool(true)
      ["msg"]=>
      string(0) ""
      ["data"]=>
      array(2) {
        ["logisticProviderID"]=>
        string(3) "YTO"
        ["txLogisticID"]=>
        string(7) "T000001"
      }
    }
   */
   ```
   * 订单取消接口[已对接]
   ```php
      // 第一个参数为物流号
      // 第二个参数为附加数据包, 包含了备注一个数据, 主要还是为了格式的统一
      $result =$yto->quickOrderCancel('LP07082300225709', ['remark' => 'test']);
      // 返回的数据
      // array(3) { ["state"]=> bool(true) ["msg"]=> string(0) "" ["data"]=> NULL }
   ```
  
* 电子面单类
   * 电子面单创建[已接通]
   ```php
    // 数据同订单创建接口一致
    // 只是调用方法变了一下, 其实内部也只是调整了一个参数和一个错误异常提示
    $order = null;
    $result = $yto->quickOrderElectronic($order);
    // 返回的数据
    /**
    array(3) {
      ["state"]=>
      bool(true)
      ["msg"]=>
      string(0) ""
      ["data"]=>
      array(8) {
        ["clientID"]=>
        string(9) "K21000119"
        ["code"]=>
        string(3) "200"
        ["distributeInfo"]=>
        array(5) {
          ["consigneeBranchCode"]=>
          array(0) {
          }
          ["packageCenterCode"]=>
          string(6) "210902"
          ["packageCenterName"]=>
          string(24) "区域件-长三角26城"
          ["printKeyWord"]=>
          array(0) {
          }
          ["shortAddress"]=>
          string(9) "330浦东"
        }
        ["logisticProviderID"]=>
        string(3) "YTO"
        ["mailNo"]=>
        string(15) "YT2990465807644"
        ["originateOrgCode"]=>
        string(6) "210045"
        ["qrCode"]=>
        string(99) "{"dtb":"330浦东","mn":"YT2990465807644","pcn":"区域件-长三角26城","rbc":"","sbc":"210045"}"
        ["txLogisticID"]=>
        string(7) "T000001"
      }
    }

   */
   ```

* 查询服务类[待规划]
   
   
