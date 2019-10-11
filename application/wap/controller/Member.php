<?php
/**
 * Member.php
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

use data\model\NsOrderExpressCompanyModel;
use data\service\Config;
use data\service\promotion\GoodsExpress;
use data\service\Shop;
use think\Request;
use think\Session;

/**
 * 会员
 */
class Member extends BaseWap
{
	
	public function __construct()
	{
		parent::__construct();
		$this->checkLogin();
	}
	
	/**
	 * 会员中心
	 */
	public function index()
	{
		$this->assign('title', "会员中心");
		$this->assign("title_before", "会员中心");
		return $this->view($this->style . 'member/index');
	}
	
	/**
	 * 会员地址管理
	 */
	public function address()
	{
		$this->assign('title', "收货地址");
		$this->assign("title_before", "收货地址");
		$this->assign('is_weixin_browser', strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger'));
		return $this->view($this->style . "member/address");
	}
	
	/**
	 * 编辑地址
	 */
	public function addressEdit()
	{
		if (!empty($_SESSION['address_pre_url'])) {
			$pre_url = $_SESSION['address_pre_url'];
		} else {
			$pre_url = '';
		}
        $data = [];
        
        $shop_id = 0;//单商户默认店铺id为0
        
        $data["shop_id"] = $shop_id;
        //交易配置
        $config_service = new Config();
        $config_info = $config_service->getShopConfig($data["shop_id"]);
        $data["order_config"] = $config_info;
        // 是否允许用户选择物流公司
        $express_compnay_list = [];
        $express_company_model = new NsOrderExpressCompanyModel();
        // 查询设置如果禁用只查询默认或者第一条
        $config = new Config();
        // 查询用户是否选择物流
        $is_able_select = $config->getConfig(0, 'ORDER_IS_LOGISTICS');
        if (!empty($is_able_select)) {
            $is_able = $is_able_select['value'];
        } else {
            $is_able = 0;
        }
        if ($is_able == 1) {
            $list = $express_company_model->getQuery([
                'shop_id' => $shop_id,
                'is_enabled' => 1
            ], 'co_id,company_name,is_default', 'orders');
        } else {
            $list = $express_company_model->getQuery([
                'shop_id' => $shop_id,
                'is_enabled' => 1,
                'is_default' => 1
            ], 'co_id,company_name,is_default', 'orders');
            if (empty($list)) {
                $new_list = $express_company_model->getFirstData([
                    'shop_id' => $shop_id,
                    'is_enabled' => 1
                ], 'orders');
                if (!empty($new_list)) {
                    $list = [];
                    $list[0] = $new_list;
                }
            }
        }
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $express_compnay_list[] = array(
                    'co_id' => $v['co_id'],
                    'company_name' => $v['company_name'],
                    'is_default' => $v['is_default'],
                    'express_fee' => 0
                );
            
            }
        }
        $express_type[] = [
            "type_id" => 1,
            "type_name" => lang("711取貨"),
            'sort' => 10
        ];
        $express_type[] = [
            "type_id" => 2,
            "type_name" => lang("全家取貨"),
            'sort' => 30
        ];
        $express_type[] = [
            "type_id" => 3,
            "type_name" => lang("宅配送貨"),
            'sort' => 50
        ];
        $data['express_type'] = $express_type;
        $data["express_company_list"] = $express_compnay_list;
        
        $shop_service = new Shop();
        //711
//        $pickup_point_list = $shop_service->getPickupPointQuery(
//            ['shop_type' => 1]
//        );
//        //全家
//        $pickup_point_list2 = $shop_service->getPickupPointQuery(
//            ['shop_type' => 2]
//        );
//        $data["pickup_point_list"] = $pickup_point_list;
//        $data["pickup_point_list2"] = $pickup_point_list2;
        
		$this->assign('data', $data);
		$this->assign("pre_url", $pre_url);
		$this->assign("title_before", lang("编辑收货地址"));
		$this->assign('title', lang("编辑收货地址"));
		return $this->view($this->style . "member/address_edit");
	}
	
	/**
	 * 店铺积分流水
	 */
	public function point()
	{
		$this->assign('title', "店铺积分流水");
		$this->assign("title_before", "店铺积分流水");
		return $this->view($this->style . 'member/point');
	}
	
	/**
	 * 会员余额
	 */
	public function balance()
	{
		$this->assign('title', "我的余额");
		$this->assign('title_before', "我的余额");
		return $this->view($this->style . 'member/balance');
	}
	
	/**
	 * 余额提现记录
	 */
	public function withdrawal()
	{
		$this->assign('title', "提现记录");
		$this->assign('title_before', "提现记录");
		return $this->view($this->style . 'member/withdrawal');
	}
	
	/**
	 * 会员优惠券
	 */
	public function coupon()
	{
		$this->assign('title', "我的优惠券");
		$this->assign('title_before', "我的优惠券");
		return $this->view($this->style . "member/coupon");
	}
	
	/**
	 * 个人资料
	 */
	public function info()
	{
		$_SESSION['bind_pre_url'] = Request::instance()->domain() . $_SERVER['REQUEST_URI'];
		$this->assign('title', "个人资料");
		$this->assign('title_before', "个人资料");
		return $this->view($this->style . "member/info");
	}
	
	/**
	 * 积分兑换余额
	 */
	public function exchange()
	{
		$this->assign('title', "积分兑换");
		$this->assign('title_before', "积分兑换");
		return $this->view($this->style . "member/exchange");
	}
	
	/**
	 * 账户列表
	 */
	public function account()
	{
		$flag = request()->get('flag', 0); // 标识，1：从会员中心的提现账号进来，0：从申请提现进来
		$_SESSION['account_flag'] = $flag;
		$this->assign('flag', $flag);
		$this->assign('title', "我的账户列表");
		$this->assign('title_before', "我的账户列表");
		
		return $this->view($this->style . "member/account");
	}
	
	/**
	 * 编辑账户
	 */
	public function accountEdit()
	{
		$this->assign('title', "编辑账户");
		$this->assign('title_before', "编辑账户");
		return $this->view($this->style . "member/account_edit");
	}
	
	/**
	 * 用户充值余额
	 */
	public function recharge()
	{
		$this->assign('title', "充值余额");
		$this->assign('title_before', "充值余额");
		return $this->view($this->style . "member/recharge");
	}
	
	/**
	 * 申请提现
	 */
	public function applyWithdrawal()
	{
		$this->assign('title', "申请提现");
		$this->assign('title_before', "申请提现");
		return $this->view($this->style . "member/apply_withdrawal");
	}
	
	/**
	 * 更改用户头像
	 */
	public function modifyFace()
	{
		$this->assign('title', "修改头像");
		$this->assign('title_before', "修改头像");
		$img_md5 = md5(time() . "niuku");
		session::set("niuku", $img_md5);
		$this->assign("niuku", $img_md5);
		return $this->view($this->style . "member/modify_face");
	}
	
	/**
	 * 我的收藏
	 */
	public function collection()
	{
		$this->assign('title', "我的收藏");
		$this->assign('title_before', "我的收藏");
		return $this->view($this->style . 'member/collection');
	}
	
	/**
	 * 我的足迹
	 */
	public function footprint()
	{
		$this->assign('title', "我的足迹");
		$this->assign('title_before', "我的足迹");
		return $this->view($this->style . "member/footprint");
	}
	
	/**
	 * 我的中奖记录
	 */
	public function winning()
	{
		$this->assign('title', "中奖记录");
		$this->assign('title_before', "中奖记录");
		return $this->view($this->style . "member/winning");
	}
	
	/**
	 * 领取奖品
	 */
	public function receivePrize()
	{
		$this->assign('title', "奖品领取");
		$this->assign('title_before', "奖品领取");
		return $this->view($this->style . "member/receive_prize");
	}
	
	/**
	 * 我的砍价
	 * @return \think\response\View
	 */
	public function bargain()
	{
		if (addon_is_exit('NsBargain') == 0) {
			$this->error('未查找到该插件');
		}
		$this->assign('title', "我的砍价");
		$this->assign('title_before', "我的砍价");
		return $this->view($this->style . "member/bargain");
	}
	
	/**
	 * 图片上传
	 */
	public function uploadImage()
	{
		if (!empty($_FILES)) {
			$res = api("System.Upload.uploadImage", [ 'file_path' => "wap_member" ]);
			return json_encode($res['data']);
		}
	}
	
	/**
	 * 会员等级
	 */
	public function level()
	{
		$this->assign('title', "会员等级");
		$this->assign('title_before', "会员等级");
		return $this->view($this->style . "member/level");
	}
	
	/**
	 * 会员签到
	 */
	public function signIn()
	{
		if (addon_is_exit('NsMemberSign')) {
			$this->assign('title', "签到有礼");
			$this->assign("title_before", "签到有礼");
			return $this->view($this->style . "member/signin");
		} else {
			$this->error('未查找到该插件');
		}
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