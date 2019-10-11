<?php
/**
 * Created by Juns <46231996@qq.com>.
 * User: jun
 * Date: 2019-07-08 15:20
 * Copyright: @比邻信息科技有限公司
 * Description:
 */

namespace data\service;

/**
 * 货币符号
 */
use data\model\AreaModel as Area;
use data\model\CityModel as City;
use data\model\DistrictModel;
use data\model\DistrictModel as District;
use data\model\NsOffpayAreaModel;
use data\model\ProvinceModel as Province;
use data\model\BlCurrencyModel as Currency;
use think\Cache;


class BlCurrency extends BaseService
{
    public function getCurrencyList() {
        return self::_currency();
    }
    private static function _currency()
    {
        static $currencies = null;
        if (!$currencies) {
            // Load currency from db
            $list = Cache::store('redis')->tag('currency')->get('getCurrencyList');
            if (empty($list)) {
                $currency = new Currency();
                $list = $currency->getQuery();
                Cache::store('redis')->tag('currency')->set("getCurrencyList", $list);
            }
            $currencies = array();
            foreach ($list as $c) {
                $currencies[$c['code']] = $c;
            }
        }
        return $currencies;
    }
    static public function format($value, $code = 'TWD', $decimals = 2) {
        $currencies = static::_currency();
        if (isset($currencies[$code])) {
            $currency = $currencies[$code];
        } else {
            $currency = $currencies['TWD'];
        }
        if (!$currency)
            return $value;
        $format_value = $currency['symbol_left'].
            number_format($value, $decimals).
            $currency['symbol_right'];
        return $format_value;
    }
    /**
     * 货币转换
     * @param $value
     * @param $from
     * @param string $to
     * @param int $decimals
     * @return float
     */
    public static function convert($value, $from, $to = 'RMB', $decimals = 2) {
        $currencies = static::_currency();
        if (isset($currencies[$from])) {
            $currency = $currencies[$from];
        }
        if (!$currency)
            return $value;
        $rmb = $value*($currencies['RMB']['value']/$currencies[$from]['value']); //中间价RMB
        if ($to == 'RMB')
            return $rmb;
        
        $currency = $currencies[$to];
        return $rmb*$currency['value'];
    }
}