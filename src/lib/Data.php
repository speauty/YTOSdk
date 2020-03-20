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
    static private $sourceData = null;
    static private $xmlDataStr = '';
    static private $initRequestData = '';
    static private $initResponseData = '';
    static private $result = '';
    static private $tmpQueryData = null;

    static private $fieldsMap = [
        'customerCode' => 'customerCode',
        'clientId' => 'customerCode',
        'clientID' => 'customerCode',
        'customerId' => 'customerCode',
        'tradeNo' => 'customerCode',
        'user_id' => 'customerCode',
        'secret_key' => 'verifyCode',
        'verifyCode' => 'verifyCode',
        'partnerId' => 'verifyCode',
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

    static public function setData(string $name, $data):void
    {
        if (!$data) Exception::throw('the data is empty, please check now.');
        self::$$name = $data;
    }

    static public function getData(string $name)
    {
        return self::$$name;
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

    static public function getXmlData(?array $data = null):string
    {
        if (!self::$xmlDataStr) {
            self::$xmlDataStr = FF::arr2Xml($data?:self::$sourceData);
        }
        return self::$xmlDataStr;
    }

    static public function buildDataDigest(Conf $conf):string
    {
        return base64_encode(md5(self::getXmlData().$conf->getConf(self::$fieldsMap['partnerId']), true));
    }

    static public function buildSign(Conf $conf):string
    {
        $secretKey = $conf->getConf(Data::getFieldMappingName('secret_key'));
        $data = [
            'user_id' => $conf->getConf(Data::getFieldMappingName('user_id')),
            'app_key' => $conf->getConf('app_key'),
            'format' => 'XML',
            'method' => $conf->getConf('method'),
//            'timestamp' => date('Y-m-d H:i:s'),
            'timestamp' => '2016-6-1 13:14:35',
            'v' => $conf->getConf('v'),
        ];
        ksort($data);

        self::$tmpQueryData = $data;
        $tmpStr = '';
        foreach (self::$tmpQueryData as $k => $v) {
            $tmpStr .= $k.$v;
        }
        Exception::throw('正在开发中');
        // 49582BD86763825DB93D14B407F32D3B
        var_dump(strtoupper(md5('1QLlIZapp_keysF1JznformatXMLmethodyto.Marketing.TransportPricetimestamp2020-03-20 17:25:54user_idYTOTESTv1')));

        die();
        return strtoupper(md5($secretKey.$tmpStr));
    }

    static public function result(\GuzzleHttp\Psr7\Response $response, bool $isElectronic = false):?array
    {
        $result = ['state' => false, 'msg' => '', 'data' => null];
        if ($response->getStatusCode() != 200) {
            $result['msg'] = $response->getReasonPhrase();
            return $result;
        }
        self::$initResponseData = $response->getBody()->getContents();
        var_dump(self::$initResponseData );
        die();
        $bodyContent = FF::xml2Arr(self::$initResponseData);
        if (!$bodyContent) {
            $result['msg'] = '未知错误';
            return $result;
        }
        if ($bodyContent['success'] == 'true') {
            $result['state'] = true;
            unset($bodyContent['success']);
            $result['data'] = $bodyContent;
        } else {
            $result['msg'] = Data::getReasonByCode($bodyContent['reason']??'', $isElectronic);
        }
        return $result;
    }
}