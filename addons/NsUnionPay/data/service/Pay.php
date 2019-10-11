<?php
/**
 * AlipayConfig.php
 *
 * Niushop商城系统 - 团队十年电商经验汇集巨献!
 * =========================================================
 * Copy right 2015-2025 山西牛酷信息科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.niushop.com.cn
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * @author : niuteam
 * @date : 2015.1.17
 * @version : v1.0.0.0
 */
namespace addons\NsUnionPay\data\service;

use data\service\BaseService;
use data\model\NsOrderPaymentModel;
use data\service\UnifyPay;

/**
 * 支付宝支付配置
 */
class Pay extends BaseService
{
    /**
     * 银联交易成功
     */
    public function backReceive($orderId, $txnTime, $queryId)
    {
    
        $unionpay = new UnionPay();
        $res = $unionpay->signatureValidate();
    
        if ($res == 1) { //签名验证通过才可
            	
            //接口查询是否数据库已更新
            $result_arr = $unionpay->query($orderId, $txnTime);
            	
            if (empty($result_arr)) return 0; //为空代表交易失败了
            	
            if ($result_arr['txnType'] == '01') {  //消费完成执行
    
                $this->onlinePay($orderId, 3, $queryId);
                return 1;
            } elseif ($result_arr['txnType'] == '04') { //退款执行
                	
            }
        }
    
        return 0;
    }
    
    /**
     * 银联前台通知验证 返回1为成功，其他都为失败
     * @param unknown $orderId
     * @param unknown $txnTime
     */
    public function frontReceive($orderId, $txnTime)
    {
    
        $unionpay = new UnionPay();
        $res = $unionpay->signatureValidate();
    
        if ($res == 1) { //签名验证通过才可
            	
            //接口查询是否数据库已更新
            $result_arr = $unionpay->query($orderId, $txnTime);
            	
            if (empty($result_arr)) return 0; //为空代表交易失败了
            	
            if ($result_arr['txnType'] == '01') {  //消费完成执行
    
                return 1;
            } elseif ($result_arr['txnType'] == '04') { //退款执行
                	
            }
        }
        return 0;
    }
    

    
}