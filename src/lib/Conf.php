<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    Conf.php
 * Created: 2020/3/19 17:19:52
 */

namespace YTOSdk\Lib;


class Conf
{
    private $customerCode = '';
    private $verifyCode = '';
    private $appKey = '';
    private $method = '';
    private $v = '1';
    private $baseUri = 'http://opentestapi.yto.net.cn';
    private $logisticProviderID = 'YTO';

    public function __construct(?array $conf)
    {
        $conf && self::setConf($conf);
    }

    public function setProperty(string $name, $data):void
    {
        if (!property_exists($this, $name)) Exception::throw('the property '.$name.' not found, please check now.');
        $this->$name = $data;
    }

    public function setConf(?array $conf):void
    {
        if (!isset($conf['customer_code']) || !$conf['customer_code']) {
            Exception::throw('the conf customer_code is not set, please check now');
        }
        if (!isset($conf['verify_code']) || !$conf['verify_code']) {
            Exception::throw('the conf verify_code is not set, please check now');
        }
        foreach ($conf as $k => $v) {
            $realName = FF::snake2SmallCamel($k);
            if (property_exists($this, $realName) && $v) $this->$realName = $v;
            unset($realName);
        }
    }

    public function getConf(string $confName):string
    {
        $confName = FF::snake2SmallCamel($confName);
        return property_exists($this, $confName)?$this->$confName:'';
    }
}