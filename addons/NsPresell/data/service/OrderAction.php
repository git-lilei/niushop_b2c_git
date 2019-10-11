<?php
/**
 * Order.php
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

namespace addons\NsPresell\data\service;

use data\model\NsOrderModel;
use data\service\Member\MemberAccount;
use data\service\UnifyPay;
use think\Log;
use data\model\NsOrderPresellModel;
use think\helper\Time;
use data\service\OrderAction as OrderActionService;
use data\model\NsOrderPaymentModel;
use data\service\OrderCreate;

/**
 * 预售订单操作类
 */
class OrderAction extends OrderActionService
{
	
    public $order;
    
    
    // 订单主表
    function __construct()
    {
        parent::__construct();
        $this->order = new NsOrderModel();
    }
    
    
    /**
     * 预售订单支付
     *
     * @param unknown $order_pay_no
     * @param unknown $pay_type
     * @param unknown $status
     */
    public function presellOrderPay($order_pay_no, $pay_type, $status = 1)
    {
        $presell_order_model = new NsOrderPresellModel();
        $presell_order_model->startTrans();
        
        try {
            
            $order_data = $presell_order_model->getInfo([
                'out_trade_no' => $order_pay_no
            ], '*');
            $presell_delivery_time = 0;
            
            if ($order_data['presell_delivery_type'] == 1) {
                $presell_delivery_time = $order_data['presell_delivery_value'];
            } else {
                $presell_delivery_time = Time::daysAfter($order_data['presell_delivery_value']);
            }
            
            // 修改订单状态
            $data = array(
                'payment_type' => $pay_type,
                'pay_time' => time(),
                'order_status' => $status,
                'presell_delivery_time' => $presell_delivery_time
            ); // 订单转为待发货状态
            
            $res = $presell_order_model->save($data, ['out_trade_no' => $order_pay_no]);
            
            $order_model = new NsOrderModel();
            $order_model->save(['order_status' => 7], ['order_id' => $order_data['relate_id']]);
            
            if ($pay_type == 10) {
                $action_data = array(
                    "uid" => $this->uid,
                    "order_id" => $order_data['relate_id'],
                    "remark" => '预售金线下支付'
                );
                // 线下支付
                $this->addOrderAction($action_data);
            } else {
                // 查询订单购买人ID
                $action_data = array(
                    "uid" => $this->uid,
                    "order_id" => $order_data['relate_id'],
                    "remark" => '预售金支付'
                );
                $this->addOrderAction($action_data);
            }
            
            $presell_order_model->commit();
            return $res;
        } catch (\Exception $e) {
            Log::write('预售订单支付失败' . $e->getMessage());
            $presell_order_model->rollback();
        }
    }
    
    /**
     * 预售订单在线支付
     */
    public function presellOrderOnLinePay($order_pay_no, $pay_type)
    {
        $retval = $this->presellOrderPay($order_pay_no, $pay_type);
        return $retval;
    }
    
    /**
     * 预售金线下支付
     *
     * @param unknown $order_id
     * @param unknown $pay_type
     */
    public function presellOrderOffLinePay($order_id, $pay_type = 10)
    {
        $presell_order_model = new NsOrderPresellModel();
        $presell_order_info = $presell_order_model->getInfo(["relate_id" => $order_id]);
        
        if(empty($presell_order_info))
            return 0;
        
        $presell_order_id = $presell_order_info["presell_order_id"];
        $this->underLinePresellOrderUpdateBalance($presell_order_id);
        
        $new_no = $this->getPresellOrderNewOutTradeNo($presell_order_id);
        
        if ($new_no) {
            
            $retval = $this->presellOrderPay($new_no, $pay_type);
            if ($retval > 0) {
                $pay = new UnifyPay();
                $pay->offLinePay($new_no, $pay_type);
            }
            return $retval;
        }
        return 0;
    }
    
    /**
     * 预售订单
     */
    public function getPresellOrderNewOutTradeNo($presell_order_id)
    {
        $presell_order_model = new NsOrderPresellModel();
        
        $out_trade_no = $presell_order_model->getInfo([
            'presell_order_id' => $presell_order_id
        ], 'out_trade_no');

        $new_no = $this->createNewOutTradeNoPresell($presell_order_id);
        
        $order_data = array(
            'out_trade_no' => $new_no
        );
        $result = $presell_order_model->save($order_data, ['presell_order_id' => $presell_order_id]);
        
        $pay_model = new NsOrderPaymentModel();
        $pay_data = array(
            "out_trade_no" => $new_no
        );
        $pay_model->save($pay_data, [ 'out_trade_no' => $out_trade_no['out_trade_no'] ]);
        
        $pay = new UnifyPay();
        $pay->modifyNo($out_trade_no['out_trade_no'], $new_no);
        return $new_no;
        
        
    }
    
    
    /**
     * 预售订单重新生成订单号
     *
     * @param unknown $presell_order_id
     */
    public function createNewOutTradeNoPresell($presell_order_id)
    {
        $presell_order = new NsOrderPresellModel();
        $order_create = new OrderCreate();
        $new_no = $order_create->createOutTradeNo();
        $data = array(
            'out_trade_no' => $new_no
        );
        $retval = $presell_order->save($data, [
            'presell_order_id' => $presell_order_id
        ]);
        if ($retval) {
            return $new_no;
        } else {
            return '';
        }
    }
    /**
     * 设置该订单为备货完成
     *
     * @param unknown $order_id
     */
    public function setOrderStockingComplete($order_id)
    {
        $order_presell_model = new NsOrderPresellModel();
        $order_presell_model->startTrans();
        
        try {
            $order_presell_info = $order_presell_model->getInfo([
                'relate_id' => $order_id
            ], '*');
            
            $order_model = new NsOrderModel();
            $order_condition = array(
                'order_id' => $order_id,
                'order_status' => 7
            );
            $order_model->save([
                'order_status' => 0
            ], $order_condition);
            
            if ($order_presell_info['is_full_payment'] == 1) {
                $order_action = new \data\service\OrderAction();
                $order_action->orderOffLinePay($order_id, $order_presell_info['payment_type'], 0); // 默认微信支付
            }
            
            $result = $order_presell_model->save([
                'order_status' => 2
            ], [
                'relate_id' => $order_id
            ]);
            
            $order_presell_model->commit();
            return $result;
        } catch (\Exception $e) {
            
            $order_presell_model->rollback();
            return $e->getMessage();
        }
    }
    
    /**
     * 预售订单重新生成交易流水号时返回之前锁定的余额
     * @param int $presell_order_id
     */
    public function createNewOutTradeNoReturnBalancePresellOrder($presell_order_id)
    {
        $pay = new NsOrderPaymentModel();
        $orderPresell = new NsOrderPresellModel();
        $order = new NsOrderModel();
        $order_presell_info = $orderPresell->getInfo([
            'presell_order_id' => $presell_order_id,
            'order_status' => 0
        ], "out_trade_no,relate_id");
        $order_info = $order->getInfo([ "order_id" => $order_presell_info['relate_id'] ], "buyer_id");
        if (!empty($order_presell_info)) {
            $pay_info = $pay->getInfo([
                'out_trade_no' => $order_presell_info['out_trade_no'],
                'pay_status' => 0
            ], "balance_money,original_money");
            
            if (!empty($pay_info) && $pay_info['balance_money'] > 0) {
                
                $member_account = new MemberAccount();
                $member_account->addMemberAccountData(0, 2, $order_info['buyer_id'], 0, $pay_info['balance_money'], 1, $presell_order_id, "订单重新生成交易号，返还锁定余额");
                
                $data = array(
                    "pay_money" => $pay_info['original_money'],
                    "balance_money" => 0
                );
                $pay->save($data, [
                    'out_trade_no' => $order_presell_info['out_trade_no']
                ]);
            }
        }
    }
    
    
    
    /**
     * 预售订单订金线下支付时判断用户是否选择使用了余额更新到预售表再执行线下支付
     * @param unknown $presell_order_id
     */
    public function underLinePresellOrderUpdateBalance($presell_order_id)
    {
        $pay = new NsOrderPaymentModel();
        // 预售订单 订金线下支付
        $presell_order = new NsOrderPresellModel();
        $presell_order_info = $presell_order->getInfo([
            'presell_order_id' => $presell_order_id,
            'order_status' => array(
                "in",
                "0"
            )
        ], "out_trade_no");
        
        if (!empty($presell_order_info)) {
            $pay_info = $pay->getInfo([
                'out_trade_no' => $presell_order_info['out_trade_no'],
                'pay_status' => 0
            ], "balance_money,pay_money");
            if (!empty($pay_info) && $pay_info['balance_money'] > 0) {
                $data = array(
                    "platform_money" => $pay_info['balance_money'],
                    "presell_pay" => $pay_info['pay_money']
                );
                $presell_order->save($data, [
                    "presell_order_id" => $presell_order_id
                ]);
            }
        }
    }
    
    
    /**
     * 预售订单订金线上支付时判断用户是否选择使用了余额更新到预售表再执行线下支付
     * @param string $out_trade_no
     */
    public function onLinePresellOrderUpdateBalance($out_trade_no)
    {
        $pay = new NsOrderPaymentModel();
        // 预售订单 订金线下支付
        $presell_order = new NsOrderPresellModel();
        
        $pay_info = $pay->getInfo([
            'out_trade_no' => $out_trade_no,
            'pay_status' => 0
        ], "balance_money,pay_money");
        if (!empty($pay_info) && $pay_info['balance_money'] > 0) {
            $data = array(
                "platform_money" => $pay_info['balance_money'],
                "presell_pay" => $pay_info['pay_money']
            );
            $presell_order->save($data, [
                "out_trade_no" => $out_trade_no
            ]);
        }
    }
}