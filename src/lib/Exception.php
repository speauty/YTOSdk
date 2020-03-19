<?php
/**
 * Author:  Speauty
 * Email:   speauty@163.com
 * File:    Exception.php
 * Created: 2020/3/19 17:02:18
 */

namespace YTOSdk\Lib;


class Exception
{
    static public function throw(string $msg, int $code = 0)
    {
        throw new \Exception($msg, $code);
    }
}