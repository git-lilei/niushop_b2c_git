<?php
/**
 * Created by Juns <46231996@qq.com>.
 * User: jun
 * Date: 2019-07-08 15:19
 * Copyright: @比邻信息科技有限公司
 * Description:
 */

namespace data\model;
use data\model\BaseModel as BaseModel;

class BlCurrencyModel extends BaseModel
{
    protected $table = 'bl_currency';
    protected $rule = [
        'currency_id'  =>  '',
    ];
    protected $msg = [
        'currency_id'  =>  '',
    ];
}