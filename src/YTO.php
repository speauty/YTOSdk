<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    YTO.php
 * Created: 2020/3/18 16:30:07
 */
declare(strict_types=1);
namespace YTOSdk;

use GuzzleHttp\Client;
use YTOSdk\Lib\FF;


class YTO
{
    private $customerCode = '';
    private $partnerId = '';
    private $baseUri = 'http://opentestapi.yto.net.cn';
    private $path = '';
    private $type = '';
    private $sourceData = null;
    private $xmlStr = '';
    private $result = null;
    private $initRequest = '';
    private $initResponse = '';
    private $isElectronic = false;
    private $logisticProviderID = 'YTO';
    private $reasonMapGeneral = [
        'S01' => '非法的XML格式',
        'S02' => '非法的数字签名',
        'S03' => '非法的物流公司',
        'S04' => '非法的通知类型',
        'S05' => '非法的通知内空',
        'S07' => '系统异常，请重试',
        'S08' => '非法的电商平台',
    ];
    private $reasonMapElectronic = [
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
    private $currentReasonMap = null;

    private function exception(string $msg): void
    {
        throw new \Exception($msg);
    }

    private function setConf(array $conf)
    {
        $this->customerCode = $conf['customer_code'] ?? '';
        $this->partnerId = $conf['partner_id'] ?? '';
        $this->path = $conf['path'] ?? '';
        $this->type = $conf['type'] ?? 'online';
        if (isset($conf['base_uri']) && $conf['base_uri']) {
            $this->baseUri = $conf['base_uri'];
        }
        if (isset($conf['is_electronic']) && $conf['is_electronic']) {
            $this->isElectronic = (bool)$conf['is_electronic'];
        }
        $this->switchReasonMap();
    }

    private function switchReasonMap():void
    {
        if ($this->isElectronic) $this->type = 'offline';
        $this->currentReasonMap = $this->isElectronic?$this->reasonMapElectronic:$this->reasonMapGeneral;
    }

    private function verifyConf()
    {
        $checkList = [
            !$this->customerCode => 'the conf named customer code is empty',
            !$this->partnerId => 'the conf named partner_id is empty',
            !$this->path => 'the conf named path is empty',
            !$this->type => 'the conf named type is empty',
            !$this->sourceData => 'the data is empty',
        ];
        foreach ($checkList as $k => $v) if ($k) $this->exception($v);
    }

    private function buildXmlData():void
    {
        $this->verifyConf();
        $this->xmlStr = FF::arr2Xml($this->sourceData);
    }

    public function __construct(?array $conf)
    {
        $this->setConf($conf);
    }

    private function buildDataDigest(): string
    {
        $this->verifyConf();
        if (!$this->xmlStr) $this->buildXmlData();
        return base64_encode(md5($this->xmlStr.$this->partnerId, true));
    }

    private function request():void
    {
        $this->verifyConf();
        $this->buildXmlData();
        $data = [
            'logistics_interface' => $this->xmlStr,
            'data_digest' => $this->buildDataDigest(),
            'clientId' => $this->customerCode,
            'type' => $this->type
        ];
        $client = new Client();
        $result = $client->request('POST', $this->baseUri.$this->path, [
            'headers' => [
                'Content-type' => 'application/x-www-form-urlencoded',
                'charset' => 'utf-8'
            ],
            'form_params' => $data
        ]);
        $this->initRequest = http_build_query($data);
        $this->result = $this->result($result);
    }

    private function result(\GuzzleHttp\Psr7\Response $response):?array
    {
        $result = ['state' => false, 'msg' => '', 'data' => null];
        if ($response->getStatusCode() != 200) {
            $result['msg'] = $response->getReasonPhrase();
            return $result;
        }
        $this->initResponse = $response->getBody()->getContents();
        $bodyContent = FF::xml2Arr($this->initResponse);
        if (!$bodyContent) {
            $result['msg'] = '未知错误';
            return $result;
        }
        if ($bodyContent['success'] == 'true' || $bodyContent['success'] == true) {
            $result['state'] = true;
            unset($bodyContent['success']);
            $result['data'] = $bodyContent;
        } else {
            $result['msg'] = $this->currentReasonMap[$bodyContent['reason']??'']??'未知错误编码: '.$bodyContent['reason'];
        }
        return $result;
    }

    public function getResult(bool $flagOnlyResult = false):array
    {
        if ($flagOnlyResult) return $this->result;
        return [
            'result' => $this->result,
            'init_request' => $this->initRequest,
            'init_response' => $this->initResponse,
        ];
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->sourceData = $data;
        return $this;
    }

    public function quickOrderGeneral(array $mustData, ?array $extData = null):array
    {
        $mustDataIdx = [
            'orderNo', 'sender', 'receiver', 'products'
        ];
        foreach ($mustDataIdx as $v) {
            $v = FF::smallCamel2Snake($v);
            if (!isset($mustData[$v]) || !$mustData[$v]) {
                $this->exception("the {$v} is empty");
            }
        }
        $addressIdx = [
            'name', 'phone', 'prov', 'city', 'address',
        ];

        foreach ($addressIdx as $v) {
            $v = FF::smallCamel2Snake($v);
            if (!isset($mustData['sender'][$v]) || !$mustData['sender'][$v]) {
                $this->exception("the sender.{$v} is empty");
            }
            if (!isset($mustData['receiver'][$v]) || !$mustData['receiver'][$v]) {
                $this->exception("the receiver.{$v} is empty");
            }
        }
        $productIdx = ['itemName', 'number', 'itemValue'];
        foreach ($productIdx as $v) {
            $v = FF::smallCamel2Snake($v);
            foreach ($mustData['products'] as $pv) {
                if (!isset($pv[$v]) || !$pv[$v]) {
                    $this->exception("the products.{$v} is empty");
                }
            }
        }
        $addressTempLate = [
            'name' => '',
            'postCode' => '',
            'phone' => '',
            'prov' => '',
            'city' => '',
            'address' => '',
        ];
        $itemTemplate = [
            'itemName' => '',
            'number' => '',
            'itemValue' => '',
        ];
        $data = [
            'clientID' => $this->customerCode,
            'logisticProviderID' => $this->logisticProviderID,
            'customerId' => $this->customerCode,
            'txLogisticID' => $mustData['order_no'],
            'tradeNo' => $this->isElectronic?$this->customerCode:$mustData['order_no'],
            'mailNo' => $extData['mailNo']??'',
            'totalServiceFee' => $extData['total_service_fee']??0,
            'codSplitFee' => $extData['cod_split_fee']??0,
            'orderType' => $extData['orderType']??1,
            'serviceType' => $extData['service_type']??0,
            'flag' => $extData['flag']??0,
            'sender' => $addressTempLate,
            'receiver' => $addressTempLate,
            'items' => null,
            'special' => $extData['special']??0,
            'remark' => $extData['remark']??''
        ];
        foreach ($addressTempLate as $k => $v) {
            $keyMap = FF::smallCamel2Snake($k);
            $data['sender'][$k] = $mustData['sender'][$keyMap]??$addressTempLate[$k];
            $data['receiver'][$k] = $mustData['receiver'][$keyMap]??$addressTempLate[$k];
        }
        foreach ($mustData['products'] as $k => $v) {
            $tmpItem = $itemTemplate;
            if (isset($v['item_name']) && $v['item_name'] && isset($v['number']) && $v['number']) {
                $tmpItem['itemName'] = $v['item_name'];
                $tmpItem['number'] = $v['number'];
                $tmpItem['itemValue'] = $v['item_value']??0;
                $data['items'][] = $tmpItem;
            }
            unset($tmpItem);
        }
        $data = ['RequestOrder' => $data];
        $this->setData($data)->request();
        return $this->getResult(true);
    }

    public function quickOrderCancel(string $orderId, ?array $extData = null):array
    {
        $data = [
            'UpdateInfo' => [
                'logisticProviderID' => $this->logisticProviderID,
                'clientID' => $this->customerCode,
                'txLogisticID' => $orderId,
                'infoType' => 'INSTRUCTION',
                'infoContent' => 'WITHDRAW',
                'remark' => $extData['remark']??''
            ]
        ];
        $this->setData($data)->request();
        return $this->getResult(true);
    }

    public function quickOrderElectronic(array $mustData, ?array $extData = null):array
    {
        $this->isElectronic = true;
        $this->switchReasonMap();
        return $this->quickOrderGeneral($mustData, $extData);
    }
}