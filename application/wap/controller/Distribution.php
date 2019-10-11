<?php
/**
 * Distribution.php
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

namespace app\wap\controller;

/**
 * 分销
 */
class Distribution extends BaseWap
{
	public function __construct()
	{
		parent::__construct();
		$this->checkLogin();
	}
	
	/**
	 * 推广中心
	 */
	public function index()
	{
		$this->assign("title_before", "分销中心");
		$this->assign("title", "分销中心");
		return $this->view($this->style . "/distribution/index");
	}
	
	/**
	 * 我的团队
	 */
	public function team()
	{
		$this->assign("title_before", "我的团队");
		$this->assign("title", '我的团队');
		return $this->view($this->style . "/distribution/team");
	}
	
	/**
	 * 区域代理申请
	 */
	public function applyRegion()
	{
		$this->assign("title_before", "区域分销申请");
		$this->assign("title", "区域分销申请");
		return $this->view($this->style . "/distribution/apply_region");
	}
	
	/**
	 * 区域代理首页
	 */
	public function region()
	{
		$this->assign("title_before", "区域分销");
		$this->assign("title", "区域分销");
		return $this->view($this->style . "/distribution/region");
	}
	
	/**
	 * 分销商申请
	 */
	public function applyPromoter()
	{
		$reapply = request()->get('reapply', '0');
		$info = api("System.Distribution.checkApplyPromoter");
		if (empty($info['data'])) {
			$this->error($info['message']);
		}
		$info = $info['data'];
		$shop_sale_money = api("System.Distribution.userConsume");
		$shop_sale_money = $shop_sale_money['data'];
		$promoter_level = [];
		foreach ($info['promoter_level'] as $k => $v) {
			if ($v['level_money'] <= $shop_sale_money) {
				$promoter_level = $v;
			}
		}
		$this->assign('reapply', $reapply);
		$this->assign('promoter_level', $promoter_level);
		$this->assign('info', $info);
		$this->assign("title_before", "申请分销商");
		$this->assign('title', "申请分销商");
		return $this->view($this->style . "/distribution/apply_promoter");
	}
	
	/**
	 * 会员对于当前店铺的佣金情况
	 */
	public function commissionShop()
	{
		$this->assign("title_before", "我的佣金");
		$this->assign("title", "我的佣金");
		return $this->view($this->style . "/distribution/commission_shop");
	}
	
	/**
	 * 会员佣金记录（明细）
	 */
	public function account()
	{
		$this->assign("title_before", "佣金记录");
		$this->assign("title", "佣金记录");
		return $this->view($this->style . "/distribution/account");
	}
	
	/**
	 * 具体项的佣金明细
	 */
	public function accountDetail()
	{
		$type_id = request()->get('type_id', 0);
		$this->assign("title_before", "佣金明细");
		if ($type_id == 0) {
			$this->assign("title", "分销佣金明细");
		} else if ($type_id == 2) {
			$this->assign("title", "区域代理佣金明细");
		} else if ($type_id == 4) {
			$this->assign("title", "股东分红明细");
		} else if ($type_id == 5) {
			$this->assign("title", "全球分红明细");
		}
		return $this->view($this->style . "/distribution/account_detail");
	}
	
	/**
	 * 股东申请
	 */
	public function applyPartner()
	{
		$this->assign("title_before", "股东申请");
		$this->assign('title', "股东申请");
		return $this->view($this->style . "/distribution/apply_partner");
	}
	
	/**
	 * 股东首页
	 */
	public function partner()
	{
		$this->assign("title_before", "股东");
		$this->assign('title', "股东");
		return $this->view($this->style . "/distribution/partner");
	}
	
	/**
	 * 申请提现
	 */
	public function toWithdraw()
	{
		$this->assign("title_before", "申请提现");
		$this->assign("title", "申请提现");
		return $this->view($this->style . "/distribution/to_withdraw");
	}
	
	/**
	 * 修改分销商店铺
	 */
	public function ShopEdit()
	{
		$this->assign('promoter_id', request()->get('promoter_id', ''));
		$this->assign("title_before", "分销店铺修改");
		$this->assign("title", "店铺修改");
		return $this->view($this->style . "/distribution/shop_edit");
	}
	
	/**
	 * 进行中佣金
	 */
	public function commissionRecording()
	{
		$this->assign("title_before", "进行中佣金");
		$this->assign("title", "进行中");
		return $this->view($this->style . "/distribution/commission_recording");
	}
	
	/**
	 * 分销商品设置
	 */
	public function goods()
	{
		$this->assign("title_before", "分销商品列表");
		$this->assign("title", "分销商品列表");
		$this->assign('category_id', request()->get('category_id', ''));
		return $this->view($this->style . "/distribution/goods");
	}
	
	/**
	 * 我的分销商品
	 */
	public function goodsUser()
	{
		$this->assign("title_before", "我的分销商品");
		$this->assign("title", "我的分销商品");
		$this->assign('category_id', request()->get('category_id', ''));
		return $this->view($this->style . "/distribution/goods_user");
	}
	
}