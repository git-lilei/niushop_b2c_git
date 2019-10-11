<?php
/**
 * DistributionShop.php
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
class DistributionShop extends BaseWap
{
	/**
	 * 我的店铺
	 */
	public function userShopGoods()
	{
		$this->checkLogin();
		$uid = request()->get('uid', '');
		if (!$uid) $uid = $this->uid;
		$info = api('System.Distribution.promoterDetailByUid', [ 'uid' => $this->uid ]);
		$info = $info['data'];
		$this->assign("title_before", $info['promoter_shop_name'] . "的店铺");
		$this->assign("title", $info['promoter_shop_name'] . '的店铺');
		
		$category_id = request()->get('category_id', ''); // 商品分类
		$brand_id = request()->get('brand_id', ''); // 品牌
		$this->assign('brand_id', $brand_id);
		$this->assign('uid', $uid);
		$this->assign('category_id', $category_id);
		return $this->view($this->style . "/distribution_shop/usershopgoods");
	}
	
	/**
	 * 店铺分享
	 */
	public function userShopQrcode()
	{
		$uid = request()->get('source_uid', '');
		if (!$uid) $this->error("当前店铺信息不存在");
		api('System.Distribution.userFxQrcode');
		$url = __URL(__URL__ . "wap/index/shopindex?source_uid=$uid");
		$this->assign('url', $url);
		$this->assign('uid', $uid);
		$this->assign("title_before", "店铺分享");
		$this->assign("title", "店铺分享");
		return $this->view($this->style . "/distribution_shop/qrcode_shop");
	}
	
}