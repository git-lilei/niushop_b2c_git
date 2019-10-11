<?php
/**
 * Created by Juns <46231996@qq.com>.
 * User: jun
 * Date: 2019-06-14 16:42
 * Copyright: @比邻信息科技有限公司
 * Description:
 */

use think\Cache;
use think\Config;
use think\Cookie;
use think\Db;
use think\Debug;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\Lang;
use think\Loader;
use think\Log;
use think\Model;
use think\Request;
use think\Response;
use think\Session;
use think\Url;
use think\View;

//在这里定义解决模板里使用中文多语言问题。
//{:lang('返回')}
/**
 * 获取语言变量值
 * @param string    $name 语言变量名
 * @param array     $vars 动态变量值
 * @param string    $lang 语言
 * @return mixed
 */
function lang($name, $vars = [], $lang = '')
{
    //收集所有不存在的语言
    
    $file = RUNTIME_PATH.'lang.php';
    if (Lang::has($name)) {
        return Lang::get($name, $vars, $lang);
    }
    if ($name)
        file_put_contents($file, $name."\n", FILE_APPEND);
    return $name;
}

function currency_format($value, $decimals = 2) {
    $code = Config::get('default_currency_code');
    
    return \data\service\BlCurrency::format($value, $code, $decimals);
}
function bl_cf($value, $decimals = 2) {
    return currency_format($value, $decimals);
}