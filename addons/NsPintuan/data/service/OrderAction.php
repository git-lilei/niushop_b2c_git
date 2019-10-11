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

namespace addons\NsPintuan\data\service;

use addons\NsPintuan\data\model\NsTuangouGroupModel;
use data\model\NsOrderModel;


/**
 * 拼团订单
 */
class OrderAction extends Order
{
	
    public $order;
    
    
    // 订单主表
    function __construct()
    {
        parent::__construct();
    }
    
    

    /**
     * 订单支付成功后
     * @param unknown $data
     */
    public function orderPaySuccess($data){
        $order_info = $data;
        if($order_info['tuangou_group_id'] > 0){
            $pintuan = new Pintuan();
            
            
            $tuangou_group = new NsTuangouGroupModel();
            $pingtuan_info = $tuangou_group->getInfo(['group_id'=>$order_info['tuangou_group_id']], "tuangou_num,status,group_uid,real_num");
            
            $order = new NsOrderModel();
            $new_data = array(
                "order_status" => 6
            );
            $order->save($new_data, [
                'order_id' => $order_info["order_id"]
            ]);
            
            // 支付成功 调用短信邮箱通知钩子 改变拼团状态
            if($pingtuan_info['group_uid'] == $order_info['buyer_id'] && $pingtuan_info['real_num'] == 0){
                // 支付成功后修改状态
                $res = $tuangou_group -> save(['status' => 1], ['group_id' => $order_info['tuangou_group_id']]);

                // 拼团发起通知用户
//                runhook("Notify", "openGroupNoticeUser", [
//                    'pintuan_group_id' => $order_info['tuangou_group_id'],
//                    'order_no' => $order_info['order_no']
//                ]);
                message("open_the_group", [
                    'pintuan_group_id' => $order_info['tuangou_group_id'],
                    'order_no' => $order_info['order_no']
                ]);//开启拼团通知

//                runhook("Notify", "openGroupNoticeBusiness", [
//                    'pintuan_group_id' => $order_info['tuangou_group_id'],
//                    'order_no' => $order_info['order_no']
//                ]);
                message("open_the_group_business", [
                    'pintuan_group_id' => $order_info['tuangou_group_id'],
                    'order_no' => $order_info['order_no']
                ]);// 拼团发起通知商家
                // 拼团成功微信模板消息
//                hook('openGroupNotice', [
//                    'pintuan_group_id' => $order_info['tuangou_group_id']
//                ]);
            }else{
                // 拼团参与通知
//                runhook("Notify", "addGroupNoticeUser", [
//                    'pintuan_group_id' => $order_info['tuangou_group_id'],
//                    'order_no' => $order_info['order_no'],
//                    'uid' => $order_info['buyer_id']
//                ]);
                message("add_the_group", [
                    'pintuan_group_id' => $order_info['tuangou_group_id'],
                    'order_no' => $order_info['order_no'],
                    'uid' => $order_info['buyer_id']
                ]);
                // 拼团参团微信模板消息
//                hook('addGroupNotice', [
//                    'pintuan_group_id' => $order_info['tuangou_group_id'],
//                    'uid' => $order_info['buyer_id']
//                ]);
            }
            
            $res = $pintuan->tuangouGroupModify($order_info['tuangou_group_id']);

            if($res <= 0){
                return error([]);
            }
            
            return success();
        }else{
            return error([]);
        }
        
    }
    
    
    /**
     * 订单支付核验
     * @param unknown $data
     */
    public function orderPayVerify($data){
        
        // 拼团支付限制
        $tuangou_group = new NsTuangouGroupModel();
        $pingtuan_info = $tuangou_group->getInfo(['group_id'=>$data['tuangou_group_id']], "tuangou_num,status,group_uid,real_num");
        
        $condition['order_status'] = ["in","1,2,3,4"];
        $condition['tuangou_group_id'] = $data['tuangou_group_id'];
        
        $order_list_count =  $this->order->getCount($condition);
        
        if($pingtuan_info['tuangou_num'] <= $order_list_count || !in_array($pingtuan_info['status'], [0, 1])){
            return error([]);
        }
        return success();
    }
    
    
}