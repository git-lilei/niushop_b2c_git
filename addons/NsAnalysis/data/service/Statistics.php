<?php
namespace addons\NsAnalysis\data\service;

use data\model\NsOrderModel;
use data\model\NsMemberModel;
use data\model\WeixinFansModel;
use data\model\NsGoodsModel;
use data\model\NsMemberViewModel;
use addons\Nsfx\data\model\NfxGoodsCommissionRateModel;
use data\service\BaseService;

class Statistics extends BaseService
{
    /**
     * 获取基础的统计数据 (仅供数据概况页用)
     */
    public function getBaseData(){
        $goods_model = new NsGoodsModel();
        $order_model = new NsOrderModel();
        $member_model = new NsMemberViewModel();
        $wxfans_model = new WeixinFansModel();
        
        $data = [
            'goods_num' => $goods_model->getCount([]),
            'order_num' => $order_model->getCount([]),
            'member_num' => $member_model->getViewCount(['is_member' => 1, 'is_system' => 0]),
            'fans_num' => $wxfans_model->getCount(['is_subscribe' => 1]),
            'today_data' => []
        ];
        
        // 数据变动趋势
        $today_data = []; // 今日数据
        $yesterday_data = []; // 昨日数据
        $today_time_section = [
            'start_time' => strtotime(date('Y-m-d', time()).' 00:00:00'),
            'end_time' => strtotime(date('Y-m-d', time()).' 23:59:59')
        ];
        $yesterday_section = [
            'start_time' => strtotime(date('Y-m-d', strtotime('-1day')).' 00:00:00'),
            'end_time' => strtotime(date('Y-m-d', strtotime('-1day')).' 23:59:59')
        ];
        
        $today_order_num_data = $this->getOrderNum($today_time_section);
        $yesterday_order_num_data = $this->getOrderNum($yesterday_section);
        $today_member_num_data = $this->getMemberNum($today_time_section);
        $yesterday_member_num_data = $this->getMemberNum($yesterday_section);
        $today_order_money_data = $this->getOrderMoney($today_time_section);
        $yesterday_order_money_data = $this->getOrderMoney($yesterday_section);
        
        $today_data = array_merge($today_order_num_data, $today_member_num_data, $today_order_money_data);
        $yesterday_data = array_merge($yesterday_order_num_data, $yesterday_member_num_data, $yesterday_order_money_data);
    
        foreach ($today_data as $key => $item){
            $data['today_data'][$key]['value'] = $item;
            if($item > $yesterday_data[$key]){
                $data['today_data'][$key]['trend'] = 'rise'; //上升
                if($yesterday_data[$key] > 0){
                    $ratio = round(($item - $yesterday_data[$key]) / $yesterday_data[$key], 2) * 100;
                }else{
                    $ratio = '100.00';
                }
                $data['today_data'][$key]['ratio'] = sprintf("%.2f", $ratio);
            }elseif($item < $yesterday_data[$key]){
                $data['today_data'][$key]['trend'] = 'decline'; //下降
                if($item > 0){
                    $ratio = round(($yesterday_data[$key] - $item) / $yesterday_data[$key], 4) * 100;
                }else{
                    $ratio = '100.00';
                }
                $data['today_data'][$key]['ratio'] = sprintf("%.2f", $ratio);
            }else{
                $data['today_data'][$key]['trend'] = 'rise';
                $data['today_data'][$key]['ratio'] = '0.00';
            }
        }
        
        return $data;
    }
    
    /**
     * 获取一段时间内订单金额数据
     * @param unknown $start_time 开始时间
     * @param unknown $end_time 结束时间
     * @param string $type 查询类型 按月 按日
     */
    public function getOrderMoneyData($start_time = '', $end_time = '', $type = 'day'){
        return $this->timeSearch($start_time, $end_time, $type, 'getOrderMoney');
    }
    
    /**
     * 获取一段时间内订单数量数据
     * @param string $start_time
     * @param string $end_time
     * @param string $type
     */
    public function getOrderNumData($start_time = '', $end_time = '', $type = 'day'){
        return $this->timeSearch($start_time, $end_time, $type, 'getOrderNum');
    }
    
    /**
     * 获取订单统计数据
     * @param unknown $condition
     */
    public function getOrderMoney($params = []){
        $order_model = new NsOrderModel();
        
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        
        $data = [];
        // 订单金额
        $data['pay_money'] = sprintf("%.2f", $order_model->getSum([
            'order_status' => ['in', '1,2,3,4'],
            'create_time' => ['between', [$start_time, $end_time]]
        ], 'order_money')); 
        // 成本
        $cost = $order_model
            ->alias('no')
            ->join([['ns_order_goods nog', 'no.order_id = nog.order_id', 'left']])
            ->where([
                'no.order_status' => ['in', '1,2,3,4'],
                'no.create_time' => ['between', [$start_time, $end_time]]
            ])
            ->sum('nog.cost_price');
        // 毛利润
        $data['profit'] = sprintf("%.2f", $data['pay_money'] - $cost); 
                
        return $data;
    }
    
    /**
     * 统计一段时间内的订单数
     * @param unknown $start_time
     * @param unknown $end_time
     */
    public function getOrderNum($params = []){
        $order_model = new NsOrderModel();
        
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        
        $data = [
            'order_num' => $order_model->getCount([
                'create_time' => ['between', [$start_time, $end_time]]
            ]),
            'pay_num' => $order_model->getCount([
                'order_status' => ['in', '1,2,3,4'],
                'create_time' => ['between', [$start_time, $end_time]]
            ]),
        ];
        
        if($data['pay_num'] > 0){
            $data['conversion_rate'] = round(($data['pay_num'] / $data['order_num']), 2) * 100;
        }else{
            $data['conversion_rate'] = 0;
        }
        return $data;
    }
    
    /**
     * 获取订单数量 按订单来源
     */
    public function getOrderCountByOrderFrom(){
        $order_model = new NsOrderModel();
        $data = [
            ['form' => 1, 'name' => '微信'],
            ['form' => 2, 'name' => '手机端'],
            ['form' => 3, 'name' => '电脑端'],
            ['form' => 4, 'name' => '小程序'],
        ];
        foreach ($data as $k => $item){
            $data[$k]['num'] = $order_model->getCount(['order_from' => $item['form']]);
        }
        return $data;
    }
    
    /**
     * 获取订单数量 按订单类型
     */
    public function getOrderCountByOrderType(){
        $order_model = new NsOrderModel();
        $data = [
            ['type' => 1, 'name' => '实物订单'],
            ['type' => 2, 'name' => '虚拟订单'],
            ['type' => 3, 'name' => '组合套餐订单'],
            ['type' => 4, 'name' => '拼团订单'],
            ['type' => 6, 'name' => '预售订单'],
            ['type' => 7, 'name' => '砍价订单'],
        ];
        foreach ($data as $k => $item){
            $data[$k]['num'] = $order_model->getCount(['order_type' => $item['type']]);
        }
        return $data;
    }
    
    /**
     * 获取一段时间内的会员数量数据
     * @param string $start_time
     * @param string $end_time
     * @param string $type
     * @return Ambigous <void, multitype:>
     */
    public function getMemberNumData($start_time = '', $end_time = '', $type = 'day'){
        return $this->timeSearch($start_time, $end_time, $type, 'getMemberNum');
    }
    
    /**
     * 获取会员统计信息
     * @param unknown $condition
     */
    public function getMemberNum($params = []){
        $member_model = new NsMemberModel();
        $order_model = new NsOrderModel();
        $wxfans_model = new WeixinFansModel();
        
        $start_time = !empty($params['start_time']) ? $params['start_time'] : strtotime('2017-01-01');
        $end_time = !empty($params['end_time']) ? $params['end_time'] : time();
        
        $data = [];
        $data['newadd_member_num'] = $member_model->getCount(['reg_time' => ['between', [$start_time, $end_time]]]);
        $data['deal_member_num'] = $order_model
            ->where(['finish_time' => ['between', [$start_time, $end_time]]])
            ->group('buyer_id')
            ->count('order_id');
        $data['newadd_fans_num'] = $wxfans_model->getCount(['subscribe_date' => ['between', [$start_time, $end_time]]]);
        $data['remove_concerns_fans_num'] = $wxfans_model->getCount(['unsubscribe_date' => ['between', [$start_time, $end_time]]]);
        
        return $data;
    }
    
    /**
     * 按时间段查询
     * @param string $start_time
     * @param string $end_time
     * @param string $type
     * @param unknown $function
     */
    private function timeSearch($start_time = '', $end_time = '', $type = 'day', $function, $params = []){
        if(!method_exists((new self()), $function)) return;
        
        if($type == 'day'){
            $start_time = !empty($start_time) ? $start_time : strtotime(date('Y-m-d', strtotime('-7day')));
            $end_time = !empty($end_time) ? $end_time : time();
        
            $data = [];
            for ($i = 0; $i < ($end_time + 1 - $start_time) / 86400; $i ++) {
                $date_start = $start_time + 86400 * $i;
                $params['start_time'] = $start = strtotime(date('Y-m-d', $date_start).'00:00:00');
                $params['end_time'] = $end = strtotime(date('Y-m-d', $date_start).'23:59:59');
                $item = [
                    'time' => date('Y-m-d', $date_start),
                    'data' => $this->$function($params)
                ];
                array_push($data, $item);
            }
            return $data;
        }elseif($type == 'month'){
            $to_time = $start_time = !empty($start_time) ? $start_time : strtotime(date('Y-m', strtotime('-2month')));
            $end_time = !empty($end_time) ? $end_time : time();
            
            $data = [];
            while ($to_time < $end_time) {
                $params['start_time'] = $start = strtotime(date('Y-m-01', $to_time).'00:00:00');
                $params['end_time'] = $end = strtotime(date('Y-m-d 23:59:59', strtotime(date('Y-m-01', $to_time).'+ 1month-1day')));
                $item = [
                    'time' => date('Y-m', $start),
                    'data' => $this->$function($params)
                ];
                array_push($data, $item);
                $to_time = strtotime(date('Y-m-d', $start).'+1month');   
            }
            return $data;
        }
    }
    
    /**
     * 获取一段时间内的商品售出数量数据
     * @param string $start_time
     * @param string $end_time
     * @param string $type
     */
    public function getGoodsSellNumData($start_time = '', $end_time = '', $type = 'day'){
        $data = $this->timeSearch($start_time, $end_time, $type, 'getGoodsSellNum');
        return $data;
    }
    
    /**
     * 获取商品售出数量
     * @param unknown $params
     */
    public function getGoodsSellNum($params = []){
        $order_model = new NsOrderModel();
        
        $start_time = $params['start_time'];
        $end_time = $params['end_time'];
        
        $data = [];
        $data['sale_num'] = $order_model
            ->alias('no')
            ->join([['ns_order_goods nog', 'no.order_id = nog.order_id', 'left']])
            ->where([
                'no.order_status' => ['in', '1,2,3,4'],
                'no.create_time' => ['between', [$start_time, $end_time]]
            ])
            ->sum('nog.num');
        return $data;
    }
    
    /**
     * 获取订单数量 按订单类型
     */
    public function getGoodsCountByGoodsType(){
        $goods_model = new NsGoodsModel();
        $data = [
            ['type' => 0, 'name' => '虚拟商品'],
            ['type' => 1, 'name' => '实物商品'],
        ];
        foreach ($data as $k => $item){
            $data[$k]['num'] = $goods_model->getCount(['goods_type' => $item['type']]);
        }
        return $data;
    }
    
    public function getGoodsCount(){
        $goods_model = new NsGoodsModel();
        $data = [
            'goods_num' => $goods_model->getCount([]),
            'in_sale_num' => $goods_model->getCount(['state' => 1]),
            'in_warehouse_num' => $goods_model->getCount(['state' => 0]),
        ];
        
        if(NS_VERSION == NS_VER_B2C_FX){
            $fx_goods_model = new NfxGoodsCommissionRateModel();
            $data['fx_goods_num'] = $fx_goods_model->getCount(['is_open' => 1]);
        }
        return $data;
    }
}