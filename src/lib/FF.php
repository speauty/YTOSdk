<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    FF.php
 * Created: 2020/3/19 16:42:26
 */

namespace YTOSdk\Lib;


/**
 * Class FF
 * @package YTOSdk\Lib
 * @description Format Factory
 */
class FF
{
    static public function smallCamel2Snake(string $str):string
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $str));
    }

    static public function snake2SmallCamel(string $str):string
    {
        return preg_replace_callback('/_+([a-z])/',function($matches){
            return strtoupper($matches[1]);
        },$str);
    }

    static public function snake2BigCamel(string $str):string
    {
        return ucfirst(preg_replace_callback('/_+([a-z])/',function($matches){
            return strtoupper($matches[1]);
        },$str));
    }

    static public function xml2Arr($xmlStr)
    {
        libxml_disable_entity_loader(true);
        return @json_decode(json_encode(simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    static public function arr2Xml(array $data, string $parentKey = ''):string
    {
        $xml = '';
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                if ($parentKey === 'items') $key = 'item';
                $val = self::arr2Xml($val, $key);
                $xml.="<{$key}>{$val}</{$key}>";
            } else {
                $xml.="<{$key}>{$val}</{$key}>";
            }
        }
        return $xml;
    }
}