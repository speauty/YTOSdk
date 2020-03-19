<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    Data.php
 * Created: 2020/3/19 17:00:18
 */

namespace YTOSdk\Lib;


class Data
{
    private $sourceData = null;

    static private $fieldsMap = [
        'customerCode' => 'customerCode',
        'verifyCode' => 'verifyCode',
        'clientID' => 'customerCode',
        'customerId' => 'customerCode',
        'tradeNo' => 'customerCode',
        'user_id' => 'customerCode',
        'secret_key' => 'customerCode',
    ];

    static private $reasonMapGeneral = [
        'S01' => '非法的XML格式',
        'S02' => '非法的数字签名',
        'S03' => '非法的物流公司',
        'S04' => '非法的通知类型',
        'S05' => '非法的通知内空',
        'S07' => '系统异常，请重试',
        'S08' => '非法的电商平台',
    ];
    static private $reasonMapElectronic = [
        'S01' => '订单报文不合法',
        'S02' => '数字签名不匹配',
        'S03' => '没有剩余单号',
        'S04' => '接口请求参数为空：logistics_interface, data_digest或clientId',
        'S05' => '唯品会专用',
        'S06' => '请求太快',
        'S07' => 'url解码失败',
        'S08' => '订单号重复：订单号+客户编码+orderType全部重复则为重复',
        'S09' => '数据入库异常',
    ];

    public function __construct(?array $data)
    {
        $this->setData(setData);
    }

    public function setData(?array $data):void
    {
        if (!$data) Exception::throw('the data is empty, please check now.');
        $this->sourceData = $data;
    }

    public function getData():?array
    {
        return $this->sourceData;
    }

    static public function getFieldMappingName(string $name):string
    {
        return self::$fieldsMap[$name]??$name;
    }

    static public function getReasonByCode(string $code, bool $isElectronic = false):string
    {
        $default = '未知异常编码: '.$code;
        return $isElectronic?self::$reasonMapElectronic[$code]??$default:self::$reasonMapGeneral[$code]??$default;
    }
}