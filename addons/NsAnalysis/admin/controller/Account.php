<?php
/**
 * Account.php
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
namespace addons\NsAnalysis\admin\controller;

use addons\NsAnalysis\data\service\Statistics;
use data\service\Goods;
use app\admin\controller\BaseController;
/**
 * 账户控制器
 */
class Account extends BaseController
{
    public $addon_view_path;
    
    public function __construct()
    {
        parent::__construct();
        $this->addon_view_path = ADDON_DIR . '/NsAnalysis/template/';
    }
    /**
     * 会员分析
     * @return
     */
    public function memberAnalysis()
    {
        $statistics = new Statistics();
        if(request()->isAjax()){
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $type = request()->post('type', 'day');
            $res = $statistics->getMemberNumData(strtotime($start_time), strtotime($end_time), $type);
            return $res;
        }
        $data = $statistics->getMemberNum();
        $this->assign('data', $data);
        return view($this->addon_view_path.$this->style . "Account/memberAnalysis.html");
    }
    
    /**
     * 交易分析
     */
    public function transactionAnalysis()
    {
        $statistics = new Statistics();
        if(request()->isAjax()){
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $type = request()->post('type', 'day');
            $tag = request()->post('tag', '');
            $params = request()->post('params', '');
            
            $res = [];
            switch ($tag) {
                case 'order_money':
                    $res = $statistics->getOrderMoneyData(strtotime($start_time), strtotime($end_time), $type);
                break;
                case 'order_num':
                    $res = $statistics->getOrderNumData(strtotime($start_time), strtotime($end_time), $type);
                break;
            }
            return $res;            
        }
        $order_from_info = $statistics->getOrderCountByOrderFrom();
        $order_type_info = $statistics->getOrderCountByOrderType();
        $this->assign('order_from_info', $order_from_info);
        $this->assign('order_type_info', $order_type_info);
        return view($this->addon_view_path. $this->style. "Account/transactionAnalysis.html");
    }
    
    /**
     * 商品分析
     * @return 
     */
    public function shopGoodsSalesList()
    {
        $statistics = new Statistics();
        if(request()->isAjax()){
            $start_time = request()->post('start_time', '');
            $end_time = request()->post('end_time', '');
            $type = request()->post('type', 'day');
            $res = $statistics->getGoodsSellNumData(strtotime($start_time), strtotime($end_time), $type);
            return $res;
        }
        $goods_num_data = $statistics->getGoodsCountByGoodsType();
        $goods_count_data = $statistics->getGoodsCount();
        $this->assign('goods_num_data', $goods_num_data);
        $this->assign('goods_count_data', $goods_count_data);
        
        $goods = new Goods();
        $goods_list = $goods->getGoodsRank(array(
            "shop_id" => $this->instance_id
        ));
        $this->assign("goods_list", $goods_list);
        return view($this->addon_view_path. $this->style. "Account/shopGoodsSalesList.html");
    }
}