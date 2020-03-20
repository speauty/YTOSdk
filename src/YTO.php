<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    PPP.php
 * Created: 2020/3/19 17:04:55
 */

namespace YTOSdk;
use GuzzleHttp\Client;
use YTOSdk\Lib\Conf;
use YTOSdk\Lib\Data;
use YTOSdk\Lib\Exception;
use YTOSdk\Lib\FF;


class YTO
{
    private $conf = null;
    private $guzzleHttpClient = null;

    private function httpClient():Client
    {
        if (!$this->guzzleHttpClient instanceof Client) {
            $this->guzzleHttpClient = new Client();
        }
        return $this->guzzleHttpClient;
    }


    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function __construct(?array $conf, ?array $data = null)
    {
        $this->conf = new Conf($conf);
        if ($data) Data::setData('sourceData', $data);
    }

    public function setData(array $data):self
    {
        Data::setData('sourceData', $data);
        return $this;
    }

    public function request(string $path, string $type, bool $isElectronic = false):self
    {
        $data = [
            'logistics_interface' => Data::getXmlData(),
            'data_digest' => Data::buildDataDigest($this->conf),
            'clientId' => $this->conf->getConf(Data::getFieldMappingName('clientId')),
            'type' => $isElectronic?'offline':$type
        ];
        $result = $this->httpClient()->request('POST', $this->conf->getConf('baseUri').$path, [
            'headers' => [
                'Content-type' => 'application/x-www-form-urlencoded',
                'charset' => 'utf-8'
            ],
            'form_params' => $data
        ]);
        Data::setData('result', Data::result($result, $isElectronic));
        Data::setData('initRequestData', http_build_query($data));
        return $this;
    }

    public function queryRequest(string $path):self
    {
        $sign = Data::buildSign($this->conf);
        $data = Data::getData('tmpQueryData');
        $data['sign'] = $sign;
        $data['param'] = Data::getXmlData();
        $result = $this->httpClient()->request('POST', $this->conf->getConf('baseUri').$path, [
            'headers' => [
                'Content-type' => 'application/x-www-form-urlencoded',
                'charset' => 'utf-8'
            ],
            'form_params' => $data
        ]);
        Data::setData('result', Data::result($result));
        Data::setData('initRequestData', http_build_query($data));
        return $this;
    }

    public function getResult(bool $flagOnlyResult = false):array
    {
        if ($flagOnlyResult) return Data::getData('result');
        return [
            'result' => Data::getData('result'),
            'init_request' => Data::getData('initRequestData'),
            'init_response' => Data::getData('initResponseData')
        ];
    }

    public function quickOrderCancel(string $orderId, ?array $extData = null):array
    {
        $data = [
            'UpdateInfo' => [
                'logisticProviderID' => $this->conf->getConf('logisticProviderID'),
                'clientID' => $this->conf->getConf(Data::getFieldMappingName('clientID')),
                'txLogisticID' => $orderId,
                'infoType' => 'INSTRUCTION',
                'infoContent' => 'WITHDRAW',
                'remark' => $extData['remark']??''
            ]
        ];
        return $this->setData($data)->request('/service/order_cancel/v1/oksM7N', $extData['type']??'offline', false)->getResult(true);
    }

    public function quickOrderGeneral(array $mustData, ?array $extData = null, bool $isElectronic= false):array
    {
        $mustDataIdx = [
            'orderNo', 'sender', 'receiver', 'products'
        ];
        foreach ($mustDataIdx as $v) {
            $v = FF::smallCamel2Snake($v);
            if (!isset($mustData[$v]) || !$mustData[$v]) {
                Exception::throw("the {$v} is empty");
            }
        }
        $addressIdx = [
            'name', 'phone', 'prov', 'city', 'address',
        ];

        foreach ($addressIdx as $v) {
            $v = FF::smallCamel2Snake($v);
            if (!isset($mustData['sender'][$v]) || !$mustData['sender'][$v]) {
                Exception::throw("the sender.{$v} is empty");
            }
            if (!isset($mustData['receiver'][$v]) || !$mustData['receiver'][$v]) {
                Exception::throw("the receiver.{$v} is empty");
            }
        }
        $productIdx = ['itemName', 'number', 'itemValue'];
        foreach ($productIdx as $v) {
            $v = FF::smallCamel2Snake($v);
            foreach ($mustData['products'] as $pv) {
                if (!isset($pv[$v]) || !$pv[$v]) {
                    Exception::throw("the products.{$v} is empty");
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
            'clientID' => $this->conf->getConf(Data::getFieldMappingName('clientID')),
            'logisticProviderID' => $this->conf->getConf('logisticProviderID'),
            'customerId' => $this->conf->getConf(Data::getFieldMappingName('clientID')),
            'txLogisticID' => $mustData['order_no'],
            'tradeNo' => $isElectronic?$this->conf->getConf(Data::getFieldMappingName('clientID')):$mustData['order_no'],
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
        return $this->setData($data)->request(
            $isElectronic?'/service/e_order_create/v1/oksM7N':'/service/order_create/v1/oksM7N',
            $isElectronic?'offline':($extData['type']??'offline'),
            $isElectronic
        )->getResult(false);
    }

    public function quickOrderElectronic(array $mustData, ?array $extData = null):array
    {
        return $this->quickOrderGeneral($mustData, $extData, true);
    }

    // yto.Marketing.TransportPrice
    public function queryTransportPrice(array $mustData)
    {
        $data = [
            'ufinterface' => [
                'Result' => [
                    'TransportInfo' => [
                        'StartProvince' => '',
                        'StartCity' => '',
                        'EndProvince' => '',
                        'EndCity' => '',
                        'GoodsWeight' => '',
                        'GoodsLength' => '',
                        'GoodsWidth' => '',
                        'GoodsHeight' => '',
                    ]
                ]
            ]
        ];
        foreach ($data['ufinterface']['Result']['TransportInfo'] as $k => &$v) {
            $tmpIdx = FF::smallCamel2Snake($k);
            if (!isset($mustData[$tmpIdx])) {
                Exception::throw("the data {$tmpIdx} not set, please check now");
            }
            $v = $mustData[$tmpIdx];
        }
        unset($v);
        $this->conf->setProperty('method', 'yto.Marketing.TransportPrice');
        $this->setData($data)->queryRequest('/service/charge_query/v1/oksM7N ')->getResult(false);
    }
}