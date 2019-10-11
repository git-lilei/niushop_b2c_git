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

namespace app\api\controller;

use addons\Nsfx\data\service\NfxCommissionConfig;
use addons\Nsfx\data\service\NfxPartner;
use addons\Nsfx\data\service\NfxPromoter;
use addons\Nsfx\data\service\NfxRegionAgent;
use addons\Nsfx\data\service\NfxShopConfig;
use addons\Nsfx\data\service\NfxUser;
use data\extend\WchatOauth;
use data\service\Address;
use data\service\Config as ConfigService;
use data\service\Member as MemberService;
use data\service\User;
use data\service\WebSite;
use data\service\Weixin;

/**
 * 分销
 */
class Distribution extends BaseApi
{
	/**
	 * 分销商信息
	 */
	public function promoterDetail()
	{
		$title = '分销商信息';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_promoter = new NfxPromoter();
		$promoter_info = $nfx_promoter->getUserPromoter($this->uid);
		if (empty($promoter_info)) {
			return $this->outMessage($title, null, -50, '非法操作');
		}
		$promoter_detail = $nfx_promoter->getPromoterDetail($promoter_info['promoter_id']);
		
		return $this->outMessage($title, $promoter_detail);
	}
	
	/**
	 * 分销商信息
	 */
	public function promoterDetailByUid()
	{
		$title = '分销商信息';
		$uid = isset($this->params['uid']) ? $this->params['uid'] : '';
		if (empty($uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_promoter = new NfxPromoter();
		$promoter_detail = $nfx_promoter->getPromoterDetailByUid($uid, 'promoter_shop_name, background_img');
		if (empty($promoter_detail)) {
			return $this->outMessage($title, null, -50, '非法操作');
		}
		return $this->outMessage($title, $promoter_detail);
	}
	
	/**
	 * 店铺分销设置
	 */
	public function shopConfig()
	{
		$shop_config = new NfxShopConfig();
		$nfx_shop_config = $shop_config->getShopConfigDetail();
		return $this->outMessage('店铺分销设置', $nfx_shop_config);
	}
	
	/**
	 * 我的团队
	 */
	public function myTeam()
	{
		$title = '我的团队';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_promoter = new NfxPromoter();
		$promoter_id = isset($this->params['promoter_id']) ? $this->params['promoter_id'] : '';
		if (empty($promoter_id)) {
			return $this->outMessage($title, null, -50, '非法操作');
		} else {
			$team_list = $nfx_promoter->getPromoterTeamListNew($promoter_id);
			return $this->outMessage($title, $team_list);
		}
	}
	
	/**
	 * 区域代理信息
	 */
	public function regionAgentConfig()
	{
		$title = '区域代理信息';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		
		$nfx_shop_config = new NfxShopConfig();
		$nfx_shop_config = $nfx_shop_config->getShopConfigDetail();
		if ($nfx_shop_config['is_regional_agent'] == 0) {
			return $this->outMessage($title, null, -10, '区域代理功能暂未开启，请联系管理人员');
		}
		
		$nfx_region_agent = new NfxRegionAgent();
		$region_config = $nfx_region_agent->getShopRegionAgentConfig($this->instance_id);
		
		if (empty($region_config)) {
			$this->outMessage($title, null, -10, '当前店铺未设置区域代理');
		}
		
		return $this->outMessage($title, $region_config);
	}
	
	/**
	 * 用户店铺消费
	 */
	public function userConsume()
	{
		$member_service = new MemberService();
		$shop_user_account = $member_service->getShopUserConsume($this->uid);
		return $this->outMessage('用户店铺消费', $shop_user_account);
	}
	
	/**
	 * 代理详情
	 */
	public function regionAgentInfo()
	{
		$nfx_region_agent = new NfxRegionAgent();
		$region_agent_info = $nfx_region_agent->getPromoterRegionAgentValidDetail($this->instance_id, $this->uid);
		return $this->outMessage('用户代理详情', $region_agent_info);
	}
	
	
	/**
	 * 申请区域代理
	 */
	public function applyRegionalAgent()
	{
		$title = '申请区域代理';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_region_agent = new NfxRegionAgent();
		$agent_type = isset($this->params['agent_type']) ? $this->params['agent_type'] : 0;
		$real_name = isset($this->params['real_name']) ? $this->params['real_name'] : "";
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : "";
		$address = isset($this->params['address']) ? $this->params['address'] : "";
		$retval = $nfx_region_agent->promoterRegionAgentApplay($this->instance_id, $this->uid, $agent_type, $real_name, $mobile, $address);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 区域代理
	 */
	public function regionAgentDetail()
	{
		$title = '区域代理';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_region_agent = new NfxRegionAgent();
		$shop_info = $nfx_region_agent->getShopRegionAgentConfig($this->instance_id);
		
		$region_agent_info = $nfx_region_agent->getPromoterRegionAgentValidDetail($this->instance_id, $this->uid);
		$address = new Address();
		$address_info = $address->getProvinceName($region_agent_info['agent_provinceid']);
		$agent_name = '省代';
		if ($region_agent_info['agent_type'] > 1) {
			$address_info .= $address->getCityName($region_agent_info['agent_cityid']);
			$agent_name = '市代';
		}
		if ($region_agent_info['agent_type'] > 2) {
			$address_info .= $address->getDistrictName($region_agent_info['agent_districtid']);
			$agent_name = '区代';
		}
		$nfx_user = new NfxUser();
		$user_account = $nfx_user->getNfxUserAccount($this->uid, $this->instance_id); // 佣金
		if ($region_agent_info["agent_type"] == 1) {
			$rate = $shop_info["province_rate"];
		} elseif ($region_agent_info["agent_type"] == 2) {
			$rate = $shop_info["city_rate"];
		} else {
			$rate = $shop_info["district_rate"];
		}
		$data = array(
			'agent_name' => $agent_name,
			'address_info' => $address_info,
			'commission_region_agent' => $user_account['commission_region_agent'],
			'rate' => $rate
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 分销商申请检测
	 */
	public function checkApplyPromoter()
	{
		$title = '分销商申请信息';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$reapply = isset($this->params['reapply']) ? $this->params['reapply'] : 0;
		// 分销商信息表
		$nfx_promoter = new NfxPromoter();
		$promoter_info = $nfx_promoter->getUserPromoter($this->uid);
		
		$shop_config = new NfxShopConfig();
		$nfx_shop_config = $shop_config->getShopConfigDetail();
		if ($nfx_shop_config['is_distribution_enable'] == 0) {
			return $this->outMessage($title, null, -10, '当前店铺未开启分销');
		}
		
		$promoter_info = empty($promoter_info) ? null : $promoter_info;
		
		// 获取店铺分销商等级
		$promoter_level = $nfx_promoter->getPromoterLevelAll($this->instance_id);
		if (empty($promoter_level)) {
			return $this->outMessage($title, null, -10, '当前店铺未设置分销商');
		}
		
		// 获取用户在本店的消费
		$member_service = new MemberService();
		$uid = $this->uid;
		$user_consume = $member_service->getShopUserConsume($uid);
		$data = array(
			'reapply' => $reapply,
			'user_consume' => $user_consume,
			'promoter_level' => $promoter_level,
			'promoter_info' => $promoter_info
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 申请分销商
	 */
	public function applyPromoter()
	{
		$title = '申请分销商';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$promoter = new NfxPromoter();
		$promoter_shop_name = isset($this->params['promoter_shop_name']) ? $this->params['promoter_shop_name'] : "";
		$retval = $promoter->promoterApplay($this->uid, $this->instance_id, $promoter_shop_name);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 会员对于当前店铺的佣金情况
	 */
	public function myCommission()
	{
		$title = '会员对于当前店铺的佣金情况';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_user = new NfxUser();
		$user_account = $nfx_user->getNfxUserAccount($this->uid, $this->instance_id);
		if (empty($user_account["commission"])) {
			$user_account["commission"] = 0.00;
		}
		if (empty($user_account["commission_locked"])) {
			$user_account["commission_locked"] = 0.00;
		}
		if (empty($user_account["commission_withdraw"])) {
			$user_account["commission_withdraw"] = 0.00;
		}
		return $this->outMessage($title, $user_account);
	}
	
	/**
	 * 具体项的佣金明细
	 */
	public function accountRecordsList()
	{
		$title = '佣金明细';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$condition['uid'] = $this->uid;
		$condition['shop_id'] = $this->instance_id;
		$type_id = isset($this->params['type_id']) ? $this->params['type_id'] : "";
		
		$nfx_user = new NfxUser();
		if ($type_id) {
			$condition['account_type'] = $type_id;
		}
		$account_records_detail = $nfx_user->getNfxUserAccountRecordsList(1, 0, $condition, 'create_time desc');
		
		if (!empty($account_records_detail)) {
			foreach ($account_records_detail as $k => $v) {
				$type_name = $v['type_name'];
			}
		} else {
			$account_type_id = $type_id;
			$account_records_type = $nfx_user->getUserAccountType($account_type_id);
			$type_name = $account_records_type['type_name'];
		}
		$data = array(
			'type_name' => $type_name,
			'account_records_detail' => $account_records_detail
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 提现记录
	 */
	public function commissionWithdrawList()
	{
		$title = '提现记录';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_user = new NfxUser();
		$condition['shop_id'] = $this->instance_id;
		$condition['uid'] = $this->uid;
		$commission_withdraw_list = $nfx_user->getUserCommissionWithdraw(1, 0, $condition, 'ask_for_date desc');
		return $this->outMessage($title, $commission_withdraw_list);
	}
	
	/**
	 * 检测股东申请
	 */
	public function checkApplyPartner()
	{
		$title = '股东申请信息';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$shop_config = new NfxShopConfig();
		$nfx_shop_config = $shop_config->getShopConfigDetail();
		$nfx_partner = new NfxPartner();
		$partner_level_list = $nfx_partner->getPartnerLevelAll($this->instance_id);
		$shop_sale_money = 0;
		$level_money_arr = array();
		if ($nfx_shop_config['is_partner_enable'] == 0) {
			return $this->outMessage($title, null, -10, '股东功能暂未开启，请联系管理人员!');
		}
		foreach ($partner_level_list as $k => $v) {
			$level_money_arr[] = $v['level_money'];
		}
		if (!empty($level_money_arr)) {
			$shop_sale_money = min($level_money_arr);
			$level_isexist = true;
		} else {
			$level_isexist = false;
		}
		
		if (!$level_isexist) {
			if ($nfx_shop_config['is_partner_enable'] == 0) {
				return $this->outMessage($title, null, -10, '暂未设置股东等级，请联系管理人员!');
			}
		}
		$partner_info = $nfx_partner->getPartnerValidDetail($this->instance_id, $this->uid);
		$agent_type = empty($partner_info) ? '2' : $partner_info['is_audit'];
		
		$data = array(
			'level_isexist' => $level_isexist,
			'shop_sale_money' => $shop_sale_money, // 申请股东最低消费金额
			'agent_type' => $agent_type,
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 申请股东
	 */
	public function applyPartner()
	{
		$title = '申请股东';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_partner = new NfxPartner();
		$retval = $nfx_partner->partnerApplay($this->instance_id, $this->uid);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 股东信息
	 */
	public function partnerDetail()
	{
		$title = '股东信息';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_partner = new NfxPartner();
		$partner_info = $nfx_partner->getPartnerValidDetail($this->instance_id, $this->uid); // 股东信息
		$partner_level_info = $nfx_partner->getPartnerLevelDetail($partner_info['partner_level']); // 等级信息
		
		$nfx_user = new NfxUser();
		$user_account = $nfx_user->getNfxUserAccount($this->uid, $this->instance_id); // 佣金
		
		$data = array(
			'level_name' => $partner_level_info['level_name'],
			'commission_rate' => $partner_level_info['commission_rate'] . '%',
			'commission_partner' => $user_account['commission_partner'],
			'commission_partner_global' => $user_account['commission_partner_global']
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 申请提现信息
	 */
	public function toWithdrawDetail()
	{
		$title = '申请提现信息';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_user = new NfxUser();
		// 佣金统计情况
		$user_account = $nfx_user->getNfxUserAccount($this->uid, $this->instance_id);
		$config_service = new ConfigService();
		$withdraw_info = $config_service->getBalanceWithdrawConfig($this->instance_id);
		if ($withdraw_info["is_use"] == 0 || $withdraw_info["value"]["withdraw_multiple"] <= 0) {
			return $this->outMessage($title, null, -10, '当前店铺未开启提现，请联系管理人员!');
		}
		$data = array(
			'user_account' => $user_account,
			'withdraw_info' => $withdraw_info['value']
		);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 申请提现
	 */
	public function applyWithdraw()
	{
		$title = '申请提现';
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$nfx_user = new NfxUser();
		// 提现
		$withdraw_no = isset($this->params['withdraw_no']) ? $this->params['withdraw_no'] : "";
		$bank_account_id = isset($this->params['bank_account_id']) ? $this->params['bank_account_id'] : "";
		$cash = isset($this->params['cash']) ? $this->params['cash'] : 0;
		
		$retval = $nfx_user->addNfxCommissionWithdraw($this->instance_id, $withdraw_no, $this->uid, $bank_account_id, $cash);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 将属性字符串转化为数组
	 */
	private function stringChangeArray($string)
	{
		if (trim($string) != "") {
			$temp_array = explode(";", $string);
			$attr_array = array();
			foreach ($temp_array as $k => $v) {
				if (strpos($v, ",") === false) {
					$attr_array = array();
					break;
				} else {
					$v_array = explode(",", $v);
					if (count($v_array) != 3) {
						$attr_array = array();
						break;
					} else {
						$attr_array[] = $v_array;
					}
				}
			}
			return $attr_array;
		} else {
			return array();
		}
	}
	
	/**
	 * 制作分销店铺二维码
	 */
	function userFxQrcode()
	{
		$title = '获取分销店铺二维码';
		if (empty($this->uid)) {
            return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
        }
        $is_applet = $this->get('is_applet', 0); // 是否小程序
		$uid = $this->uid;
		//生成包含uid链接二维码
		$upload_path = "upload/qrcode/promote_qrcode/user_fx"; // 推广二维码手机端展示
		if (!file_exists($upload_path)) {
			$mode = intval('0777', 8);
			mkdir($upload_path, $mode, true);
		}
		if ($is_applet == 1) {
            $wchat_oauth = new WchatOauth();
            $scene = 'sourceid_' . $uid;
            $page = 'pages/index/index';
            $path = $wchat_oauth->getAppletQrcode($scene, $page, true);
            if ($path == -50) {
                return $this->outMessage($title, '', -50, '商家未配置小程序');
            } else if ($path == -10) {
                return $this->outMessage($title, '', -10, '二维码生成失败，请检查该二维码指向页面是否在小程序线上版本中存在');
            }
        } else {
            $path = getQRcode(__URL__ . '/wap/index/shopindex?source_uid=' . $this->uid, $upload_path, 'shop_goods_qrcode_' . $this->uid);
        }
		
		$user = new User();
		$member_info = $user->getUserDetail($uid);
		
		// 用户头像
		$config = new ConfigService();
		$defaultImages = $config->getDefaultImages($this->instance_id);
		$user_headimg = $member_info["user_headimg"];
		if (!strstr($user_headimg, "http://") && !strstr($user_headimg, "https://")) {
			if (!file_exists($user_headimg)) {
				$user_headimg = $defaultImages["value"]["default_headimg"];
			}
		}
		// 获取所在店铺信息
		$web = new WebSite();
		$shop_info = $web->getWebDetail();
		$shop_logo = $shop_info["logo"];
		
		//分销店铺名
		$service = new NfxPromoter();
		$info = $service->getPromoterDetailByUid($uid, 'promoter_shop_name');
		
		$weixin = new Weixin();
		$data = $weixin->getWeixinQrcodeConfig($uid, 2);//2-分销店铺二维码
		
		$img_path = 'upload/qrcode/promote_qrcode/user_fx/shop_goods_qrcode_' . $this->uid . '.png';
		$this->showUserQecode($path, $path, $user_headimg, $shop_logo, $info['promoter_shop_name'] . "的店铺", $data, $img_path);
		
		return $this->outMessage($title, $img_path);
		
	}
	
	/**
	 * 制作推广二维码
	 */
	function showUserQecode($path, $thumb_qrcode, $user_headimg, $shop_logo, $user_name, $data, $create_path)
	{
		//暂无法生成
		if (!strstr($path, "http://") && !strstr($path, "https://")) {
			if (!file_exists($path)) {
				$path = "public/static/images/qrcode_bg/qrcode_bg.png";
			}
		}
		$image = \think\Image::open($path);
		$image->thumb(288, 288, \think\Image::THUMB_CENTER)->save($thumb_qrcode);
		$dst = $data["background"];
		if (!strstr($dst, "http://") && !strstr($dst, "https://")) {
			if (!file_exists($dst)) {
				$dst = "public/static/images/qrcode_bg/qrcode_bg.png";
			}
		}
		list ($max_width, $max_height) = getimagesize($dst);
		$dests = imagecreatetruecolor(640, 1134);
		$dst_im = getImgCreateFrom($dst);
		imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
		imagedestroy($dst_im);
		$ename=getimagesize($thumb_qrcode);
        $ename=explode('/',$ename['mime']);
        $ext=$ename[1];
        switch($ext){
            case "png":
            $src_im=imagecreatefrompng($thumb_qrcode);
            break;
        case "jpeg":
            $src_im=imagecreatefromjpeg($thumb_qrcode);
            break;
        case "jpg":
            $src_im=imagecreatefromjpeg($thumb_qrcode);
            break;
        case "gif":
            $src_im=imagecreatefromgif($thumb_qrcode);
            break;
        }
		$src_info = getimagesize($thumb_qrcode);
		imagecopy($dests, $src_im, $data["code_left"] * 2, $data["code_top"] * 2, 0, 0, $src_info[0], $src_info[1]);
		imagedestroy($src_im);
		if (!strstr($user_headimg, "http://") && !strstr($user_headimg, "https://")) {
			if (!file_exists($user_headimg)) {
				$user_headimg = "public/static/images/qrcode_bg/head_img.png";
			}
		}
		$user_headimg_info = getimagesize($user_headimg);
		$src_im_1 = getImgCreateFrom($user_headimg);
		$src_info_1 = getimagesize($user_headimg);
		imagecopy($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, $src_info_1[0], $src_info_1[1]);
		imagedestroy($src_im_1);
		if ($data['is_logo_show'] == '1') {
			if (!strstr($shop_logo, "http://") && !strstr($shop_logo, "https://")) {
				if (!file_exists($shop_logo)) {
					$shop_logo = "public/static/images/logo.png";
				}
			}
			$src_im_2 = getImgCreateFrom($shop_logo);
			$src_info_2 = getimagesize($shop_logo);
			imagecopy($dests, $src_im_2, $data['logo_left'] * 2, $data['logo_top'] * 2, 0, 0, $src_info_2[0], $src_info_2[1]);
			imagedestroy($src_im_2);
		}
		if ($user_name == "") {
			$user_name = "店铺";
		}
		$rgb = hColor2RGB($data['nick_font_color']);
		$bg = imagecolorallocate($dests, $rgb['r'], $rgb['g'], $rgb['b']);
		$name_top_size = $data['name_top'] * 2 + $data['nick_font_size'];
		imagefttext($dests, $data['nick_font_size'], 0, $data['name_left'] * 2, $name_top_size, $bg, ROOT_PATH . "/public/static/font/Microsoft.ttf", $user_name);
		header("Content-type: image/jpeg");
		ob_clean();
		if ($create_path == "") {
			imagejpeg($dests);
		} else {
			imagejpeg($dests, $create_path);
		}
	}
	
	/**
	 * 分销商店铺修改
	 */
	public function updatePromoter()
	{
		$promoter_id = isset($this->params['promoter_id']) ? $this->params['promoter_id'] : '';
		$promoter_shop_name = isset($this->params['promoter_shop_name']) ? $this->params['promoter_shop_name'] : '';
		$background_img = isset($this->params['background_img']) ? $this->params['background_img'] : '';
		if (!$promoter_id) {
			return $this->outMessage('分销商店铺修改', null, '-9999', "无法获取信息");
		}
		$nfx_promoter = new NfxPromoter();
		$promoter_info = $nfx_promoter->getPromoterDetailByUid($this->uid);
		
		if ($promoter_info['promoter_id'] != $promoter_id) {
			return $this->outMessage('分销商店铺修改', null, '-10', "权限不足");
		}
		
		$res = $nfx_promoter->modifyShop([ 'promoter_shop_name' => $promoter_shop_name, 'background_img' => $background_img ], [ 'promoter_id' => $promoter_id ]);
		return $this->outMessage('分销商店铺修改', $res);
	}
	
	/**
	 * 分销商品
	 */
	public function distributionGoodsList()
	{
		$title = "分销商品";
		$page_index = isset($this->params['page']) ? $this->params['page'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : '';
		$brand_id = isset($this->params['brand_id']) ? $this->params['brand_id'] : "";// 品牌
		$order = isset($this->params['obyzd']) ? $this->params['obyzd'] : "";// 商品排序分类,order by ziduan
		$sort = isset($this->params['st']) ? $this->params['st'] : "desc";// 商品排序分类 sort
		$uid = isset($this->params['uid']) ? $this->params['uid'] : "0";// 推广员uid
		$min_price = isset($this->params['mipe']) ? $this->params['mipe'] : ""; // 价格区间,最小min_price
		$max_price = isset($this->params['mape']) ? $this->params['mape'] : ""; // 最大 max_price
		$attr = isset($this->params['attr']) ? $this->params['attr'] : ""; // 属性值
		$spec = isset($this->params['spec']) ? $this->params['spec'] : ""; // 规格值
		$search_text = isset($this->params['search_text']) ? $this->params['search_text'] : ""; // 搜索值
		$type = isset($this->params['type']) ? $this->params['type'] : "add"; // 查询类型 add添加 selected查询已选的
		
		$attr_array = $this->stringChangeArray($attr);
		// 规格转化为数组
		if ($spec != "") {
			$spec_array = explode(";", $spec);
		} else {
			$spec_array = array();
		}
		if ($order != "") {
			if ($order != "ng.sales" && $order != "ng.is_new" && $order != "ng.promotion_price") {
				// 非法参数进行过滤
				$orderby = "ng.sort desc, ngcr.distribution_commission_rate desc, ng.create_time desc";
			} else {
				$orderby = $order . " " . $sort;
			}
		} else {
			$orderby = "ng.sort desc, ngcr.distribution_commission_rate desc";
		}
		
		$user_goods = '';
		if (!empty($uid)) {
			$nfx_promoter = new NfxPromoter();
			$promoter_info = $nfx_promoter->getUserPromoter($uid);
			if (!empty($promoter_info['promoter_id'])) {
				$user_goods = $nfx_promoter->getPromoterGoodsIds($promoter_info['promoter_id']);
				$user_goods = $user_goods ? $user_goods : '99999';
			}
		}
		$seletced_good_ids = $type == 'selected' ? $user_goods : '';
		
		$nfxCommission = new NfxCommissionConfig();
		$list = $nfxCommission->getGoodsListByConditionTwo($uid, $category_id, $brand_id, $min_price, $max_price, $page_index, $page_size, $orderby, $attr_array, $spec_array, $search_text, $seletced_good_ids);
		
		foreach ($list['data'] as $k => $v) {
			if (strpos(',' . $user_goods . ',', ',' . $v['goods_id'] . ',') !== false) {
				$list['data'][ $k ]['is_select'] = 1;
			} else {
				$list['data'][ $k ]['is_select'] = 0;
			}
		}
		
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 添加推广员商品
	 */
	public function addPromoterGoods()
	{
		$title = "分销商品";
		$goods_id_str = isset($this->params['goods_id_str']) ? $this->params['goods_id_str'] : "";
		if (!$goods_id_str || !$this->uid) return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		
		$nfx_promoter = new NfxPromoter();
		$promoter_info = $nfx_promoter->getUserPromoter($this->uid);
		
		$res = $nfx_promoter->addPromoterGoods($promoter_info['promoter_id'], $goods_id_str);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 分销商品数量
	 */
	public function distributionGoodsCount()
	{
		$title = "分销商品数量";
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$category_id = isset($this->params['category_id']) ? $this->params['category_id'] : '';
		$uid = isset($this->params['uid']) ? $this->params['uid'] : "0";// 推广员uid
		
		$condition = [];
		if ($category_id != "") {
			// 商品分类Id
			$condition["ng.category_id"] = $category_id;
		}
		
		$condition['ngcr.is_open'] = 1;
		$condition['ng.goods_id'] = [ 'neq', 'null' ];
		
		$nfx_promoter = new NfxPromoter();
		$promoter_info = $nfx_promoter->getUserPromoter($this->uid);
		
		if ($promoter_info) {
			$user_goods = $nfx_promoter->getPromoterGoodsIds($promoter_info['promoter_id']);
			if ($uid) {
				$condition['ng.goods_id'] = [ 'in', $user_goods ];
			}
		}
		
		$nfxCommission = new NfxCommissionConfig();
		$count = $nfxCommission->getGoodsrViewCount($condition);
		return $this->outMessage('分销商品数量', $count);
	}
	
	/**
	 * 删除推广员商品
	 */
	public function deletePromoterGoods()
	{
		$title = "删除分销商品";
		$goods_id_str = isset($this->params['goods_id_str']) ? $this->params['goods_id_str'] : "";
		if (!$goods_id_str || !$this->uid) return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		
		$nfx_promoter = new NfxPromoter();
		$promoter_info = $nfx_promoter->getUserPromoter($this->uid);
		
		$res = $nfx_promoter->delectPromoterGoodsId($goods_id_str, $promoter_info['promoter_id']);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 佣金列表
	 */
	public function commissionDistributionList()
	{
		$title = "获取佣金列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		
		$page_index = isset($this->params['page']) ? $this->params['page'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$type = isset($this->params['type']) ? $this->params['type'] : 'promoter_commission';
		
		$fx_user = new NfxUser();
		$fx_user_info = $fx_user->getShopMemberAssociation($this->uid, 0);
		if (empty($fx_user_info)) return $this->outMessage($title, null, -1, '未获取到推广员信息');
		
		$list = [];
		$condition = [ 'is_issue' => 0 ];
		switch ($type) {
			case 'promoter_commission':
				// 推广员佣金
				$condition['promoter_id'] = $fx_user_info['promoter_id'];
				$nfx_promoter = new NfxPromoter();
				$list = $nfx_promoter->getCommissionPageList($page_index, $page_size, $condition, "create_time desc");
				break;
			case 'region_agent_commission':
				// 区域代理佣金
				$condition['promoter_id'] = $fx_user_info['promoter_id'];
				$nfx_region_agent = new NfxRegionAgent();
				$list = $nfx_region_agent->getCommissionPageList($page_index, $page_size, $condition, "create_time desc");
				break;
			case 'partner_commission':
				// 股东佣金
				$condition['partner_id'] = $fx_user_info['partner_id'];
				$nfx_partner = new NfxPartner();
				$list = $nfx_partner->getCommissionPageList($page_index, $page_size, $condition, "create_time desc");
				break;
		}
		return $this->outMessage($title, $list);
	}
}