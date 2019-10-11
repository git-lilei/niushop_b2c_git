<?php
/**
 * Member.php
 * NiuShop商城系统 - 团队十年电商经验汇集巨献!
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

namespace app\web\controller;

use data\service\OrderQuery;
use think\Cookie;

/**
 * 会员控制器
 */
class Member extends BaseWeb
{
	
	public function __construct()
	{
		parent::__construct();
		// 如果没有登录的话让其先登录
		$this->checkLogin();
	}
	
	//会员中心
	public function index()
	{
		$this->assign("title_before", "会员中心");
		return $this->view($this->style . 'member/index');
	}
	
	/**
	 * 收货地址列表
	 */
	public function address()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign("title_before", "收货地址");
		return $this->view($this->style . "member/address");
	}
	
	/**
	 * 编辑地址
	 */
	public function addressEdit()
	{
		$address_id = input('address_id', 0);
		$this->assign('address_id', $address_id);
		$this->assign("title_before", "编辑收货地址");
		return $this->view($this->style . "member/address_edit");
	}
	
	/**
	 * 我的订单
	 */
	public function order()
	{
		$this->assign("title_before", "我的订单");
		return $this->view($this->style . 'member/order');
	}
	
	/**
	 * 订单详情
	 */
	public function orderDetail()
	{
		$this->assign("title_before", "订单详情");
		return $this->view($this->style . 'member/order_detail');
	}
	
	/**
	 * 商品收藏
	 */
	public function collection()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign('title_before', '商品收藏');
		return $this->view($this->style . 'member/collection');
	}
	
	/**
	 * 待付款订单
	 */
	public function payment()
	{
		$create_data = $this->orderCreateData();
		$this->assign("create_data", $create_data);
		$this->assign("title_before", "待付款订单");
		return $this->view($this->style . 'member/payment');
	}
	
	/**
	 * 退款/退货/维修订单列表
	 */
	public function refund()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign("title_before", "退款订单列表");
		return $this->view($this->style . 'member/refund');
	}
	
	/**
	 * 申请退款
	 */
	public function refundDetail()
	{
		$order_goods_id = request()->get('order_goods_id', 0);
		if (!is_numeric($order_goods_id) || $order_goods_id == 0) {
			$this->error("没有获取到退款信息");
		}
		$this->assign("order_goods_id", $order_goods_id);
		$this->assign("title_before", "退款详情");
		return $this->view($this->style . "member/refund_detail");
	}
	
	/**
	 * 商品评价/晒单
	 */
	public function evaluate()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign("title_before", "商品评价列表");
		return $this->view($this->style . 'member/evaluate');
	}
	
	/**
	 * 商品评价
	 */
	public function evaluateEdit()
	{
		$this->assign("title_before", "商品评价");
		return $this->view($this->style . 'member/evaluate_edit');
	}
	
	/**
	 * 用户信息
	 */
	public function info()
	{
		$this->assign("title_before", "个人资料");
		return $this->view($this->style . 'member/info');
	}
	
	/**
	 * 优惠券
	 */
	public function coupon()
	{
		$type = input('type', '');
		$this->assign('type', $type);
		$this->assign("title_before", "我的优惠券");
		return $this->view($this->style . 'member/coupon');
	}
	
	/**
	 * 会员积分流水
	 */
	public function point()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign("title_before", "我的积分");
		return $this->view($this->style . 'member/point');
	}
	
	/**
	 * 会员余额流水
	 */
	public function balance()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign("title_before", "我的余额");
		return $this->view($this->style . 'member/balance');
	}
	
	/**
	 * 余额提现
	 */
	public function balanceWithdrawal()
	{
		$this->assign('title_before', '余额提现');
		return $this->view($this->style . "member/balance_withdrawal");
	}
	
	/**
	 * 提现记录
	 */
	public function withdrawal()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign('title_before', '提现记录');
		return $this->view($this->style . 'member/withdrawal');
	}
	
	/**
	 * 余额积分相互兑换
	 */
	public function exchange()
	{
		$amount = request()->post('amount', 0);
		if ($amount > 0) {
			$this->assign("amount", $amount);
			$this->assign("title_before", "余额积分兑换");
			return $this->view($this->style . 'member/exchange');
		} else {
			$redirect = __URL(__URL__ . "/member/index");
			$this->redirect($redirect);
		}
	}
	
	/**
	 * 账号安全
	 */
	public function security()
	{
		$type = request()->get('type', '');
		$this->assign("type", $type);
		$this->assign("title_before", "账户安全");
		return $this->view($this->style . "member/security");
	}
	
	/**
	 * 我的足迹
	 */
	public function footprint()
	{
		$this->assign("title_before", "我的足迹");
		return $this->view($this->style . "member/footprint");
	}
	
	/**
	 * 购买的商品列表(虚拟商品)
	 */
	public function goods()
	{
		$page_index = request()->get('page', 1);
		$this->assign('page_index', $page_index);
		$this->assign("title_before", "购买商品列表");
		return $this->view($this->style . "member/goods");
	}
	
	/**
	 * 申请售后
	 */
	public function afterSale()
	{
		$order_goods_id = request()->get('order_goods_id', 0);
		if (!is_numeric($order_goods_id) || $order_goods_id == 0) {
			$this->error("没有获取到退款信息");
		}
		$this->assign("order_goods_id", $order_goods_id);
		$this->assign("title_before", "申请售后");
		return $this->view($this->style . "member/aftersale");
	}
	
	/**
	 * 查看物流
	 */
	public function logistics()
	{
		$order_id = request()->get('order_id', 0);
		if ($order_id == 0) {
			$this->error("没有获取到订单信息");
		}
		$this->assign("order_id", $order_id);
		$this->assign("title_before", "查看物流");
		return $this->view($this->style . 'member/logistics');
	}
	
	/**
	 * 图片上传
	 */
	public function uploadImage()
	{
		if (!empty($_FILES)) {
			$res = api("System.Upload.uploadImage", [ 'file_path' => "member" ]);
			return json_encode($res['data']);
		}
	}
	
	/**
	 * 获取创建所需数据
	 */
	public function orderCreateData()
	{
		$data = Cookie::get('orderCreateData');
		$data = json_decode($data, true);
		return $data;
	}
	
	/**
	 * 更新会员信息session
	 */
	public function updateMemberInfo()
	{
		if (request()->isAjax()) {
			$member_detail = api("System.Member.memberInfo");
			if ($member_detail['code'] == 0) {
				session("niu_member_detail", $member_detail['data']);
			}
		}
	}
}