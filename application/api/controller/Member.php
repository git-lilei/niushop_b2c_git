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

namespace app\api\controller;

use addons\NsBargain\data\service\Bargain;
use addons\Nsfx\data\service\NfxPromoter;
use data\extend\WchatOauth;
use data\service\Address;
use data\service\Album;
use data\service\Article;
use data\service\Config as ConfigService;
use data\service\Goods as GoodsService;
use data\service\Member as MemberService;
use data\service\Member\MemberAccount as MemberAccountService;
use data\service\OrderQuery;
use data\service\Promotion as PromotionService;
use data\service\Shop as ShopService;
use data\service\UnifyPay;
use data\service\User;
use data\service\WebSite;
use data\service\Weixin;
use think\Request;
use think\Session;

/**
 * 会员
 */
class Member extends BaseApi
{
	/**
	 * 获取会员信息
	 */
	public function memberInfo()
	{
		$title = "获取会员信息";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		
		$sys_user = new User();
		$member = new MemberService();
		
		$field = 'uid,user_name,user_status,user_headimg,user_tel,user_qq,qq_openid,qq_info,user_email,wx_openid,wx_is_sub,wx_info,real_name,sex,location,nick_name,wx_unionid,birthday';
		$user_info = $sys_user->getUserInfoByCondition([ 'uid' => $this->uid ], $field);
		$member_info = $member->getMemberInfo([], 'member_level,member_label');
		$member_detail = [];
		if (!empty($user_info)) {
			if ($user_info["user_status"] == 0) {
				$user_info["user_status_name"] = "锁定";
			} else {
				$user_info["user_status_name"] = "正常";
			}
			if (!empty($user_info["birthday"])) {
				$user_info["birthday"] = date('Y-m-d', $user_info["birthday"]);
			}
			$member_detail["user_info"] = $user_info;
		}
		if (!empty($member_info)) {
			$member_detail['member_level'] = $member_info['member_level'];
			$member_detail['member_label'] = $member_info['member_label'];
		}
		return $this->outMessage($title, $member_detail);
	}
	
	/**
	 * 制作推广二维码
	 */
	function showUserQrcode()
	{
		$title = '获取推广二维码';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$uid = $this->uid;
		// 读取生成图片的位置配置
		$config = new ConfigService();
		$weixin = new Weixin();
		$data = $weixin->getWeixinQrcodeConfig($uid);
		$user = new User();
		$member_info = $user->getUserDetail($uid);
		// 获取所在店铺信息
		$web = new WebSite();
		$shop_info = $web->getWebDetail();
		$shop_logo = $shop_info["logo"];
		
		// 获取默认头像
		$defaultImages = $config->getDefaultImages(0);
		
		$upload_path = "upload/qrcode/promote_qrcode/applet_user"; // 推广二维码手机端展示
		if (!file_exists($upload_path)) {
			$mode = intval('0777', 8);
			mkdir($upload_path, $mode, true);
		}
		$wchat_oauth = new WchatOauth();
		$scene = 'sourceid_' . $uid;
		$page = 'pages/index/index';
		$path = $wchat_oauth->getAppletQrcode($scene, $page, true);
		if ($path == -50) {
			return $this->outMessage($title, '', -50, '商家未配置小程序');
		} else if ($path == -10) {
		    return $this->outMessage($title, '', -10, '二维码生成失败，请检查该二维码指向页面是否在小程序线上版本中存在');
        }
		
		// 定义中继二维码地址
		$thumb_qrcode = $upload_path . '/thumb_' . 'qrcode_' . $uid . '_' . $this->instance_id . '.png';
		$image = \think\Image::open($path);
		// 生成一个固定大小为360*360的缩略图并保存为thumb_....jpg
		$image->thumb(288, 288, \think\Image::THUMB_CENTER)->save($thumb_qrcode);
		// 背景图片
		$dst = $data["background"];
		if (!strstr($dst, "http://") && !strstr($dst, "https://")) {
			if (!file_exists($dst)) {
				$dst = "public/static/images/qrcode_bg/qrcode_bg.png";
			}
		}
		// 生成画布
		list ($max_width, $max_height) = getimagesize($dst);
		$dests = imagecreatetruecolor($max_width, $max_height);
		$dst_im = getImgCreateFrom($dst);
		imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
		imagedestroy($dst_im);
		// 并入二维码
		$src_im = getImgCreateFrom($thumb_qrcode);
		$src_info = getimagesize($thumb_qrcode);
		imagecopy($dests, $src_im, $data["code_left"] * 2, $data["code_top"] * 2, 0, 0, $src_info[0], $src_info[1]);
		imagedestroy($src_im);
		
		// 并入用户头像
		$user_headimg = $member_info["user_headimg"];
		if (!strstr($user_headimg, "http://") && !strstr($user_headimg, "https://")) {
			if (!file_exists($user_headimg)) {
				$user_headimg = $defaultImages["value"]["default_headimg"];
			}
		}
		$src_im_1 = getImgCreateFrom($user_headimg);
		
		if (empty($src_im_1)) {
			$user_headimg = $defaultImages["value"]["default_headimg"];
			$src_im_1 = getImgCreateFrom($user_headimg);
		}
		$src_info_1 = getimagesize($user_headimg);
		imagecopyresampled($dests, $src_im_1, $data['header_left'] * 2, $data['header_top'] * 2, 0, 0, 80, 80, $src_info_1[0], $src_info_1[1]);
		imagedestroy($src_im_1);
		
		// 并入网站logo
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
		// 并入用户姓名
		$rgb = hColor2RGB($data['nick_font_color']);
		$bg = imagecolorallocate($dests, $rgb['r'], $rgb['g'], $rgb['b']);
		$name_top_size = $data['name_top'] * 2 + $data['nick_font_size'];
		@imagefttext($dests, $data['nick_font_size'], 0, $data['name_left'] * 2, $name_top_size, $bg, "public/static/font/Microsoft.ttf", $member_info["nick_name"]);
		@unlink($path);
		ob_clean();
		$img_path = 'upload/qrcode/promote_qrcode/spreadQrcode' . $this->uid . '.jpg';
		imagejpeg($dests, $img_path);
		return $this->outMessage($title, $img_path);
	}
	
	/**
	 * 会员地址管理
	 */
	public function memberAddressList()
	{
		$title = "获取会员地址";
		if (empty($this->uid)) {
			return $this->outMessage($title, null, '-9999', "无法获取会员登录信息");
		}
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		
		$applet_member = new MemberService();
		$list = $applet_member->getMemberExpressAddressList($page_index, $page_size, [
			'uid' => $this->uid
		], 'is_default desc,id desc');
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 添加地址
	 */
	public function addAddress()
	{
		$title = "添加会员地址,注意传入省市区id";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$applet_member = new MemberService();
		
		$consigner = isset($this->params['consigner']) ? $this->params['consigner'] : "";
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : "";
		
		if (empty($mobile)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数mobile");
		}
		
		$phone = isset($this->params['phone']) ? $this->params['phone'] : "";
		$province = isset($this->params['province']) ? $this->params['province'] : "";
		
		if (empty($province)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数province");
		}
		
		$city = isset($this->params['city']) ? $this->params['city'] : "";
		
		if (empty($city)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数city");
		}
		
		$district = isset($this->params['district']) ? $this->params['district'] : "";
		$address = isset($this->params['address']) ? $this->params['address'] : "";
		
		if (empty($address)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数address");
		}
		
		$zip_code = isset($this->params['zip_code']) ? $this->params['zip_code'] : "";
		$alias = isset($this->params['alias']) ? $this->params['alias'] : "";
        $shipping_type = isset($this->params['shipping_type']) ? $this->params['shipping_type'] : "0";
        $shipping_company = isset($this->params['shipping_company']) ? $this->params['shipping_company'] : "0";
        $shipping_pick_up = isset($this->params['shipping_pick_up']) ? $this->params['shipping_pick_up'] : "0";
        $pick_up_id = 0;
        $shipping_company_id = 0;
        if ($shipping_type < 3) {
            //超商
            $pick_up_id = $shipping_pick_up;
        } else {
            $shipping_company_id = $shipping_company;
        }
		$data = array(
			'uid' => $this->uid,
			'consigner' => $consigner,
			'mobile' => $mobile,
			'phone' => $phone,
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'address' => $address,
			'zip_code' => $zip_code,
			'alias' => $alias,
			'is_default' => 0,
            'pick_up_id' => $pick_up_id,
            'shipping_company_id' => $shipping_company_id
   
		);
		$retval = $applet_member->addMemberExpressAddress($data);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 修改会员地址
	 */
	public function updateAddress()
	{
		$title = "修改会员地址,注意传入省市区id";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$applet_member = new MemberService();
		
		$id = isset($this->params['id']) ? $this->params['id'] : "";
		
		if (empty($id)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数id");
		}
		
		$consigner = isset($this->params['consigner']) ? $this->params['consigner'] : "";
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : "";
		
		if (empty($mobile)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数mobile");
		}
		
		$phone = isset($this->params['phone']) ? $this->params['phone'] : "";
		$province = isset($this->params['province']) ? $this->params['province'] : "";
		
		if (empty($province)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数province");
		}
		
		$city = isset($this->params['city']) ? $this->params['city'] : "";
		
		if (empty($city)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数city");
		}
		
		$district = isset($this->params['district']) ? $this->params['district'] : "";
		$address = isset($this->params['address']) ? $this->params['address'] : "";
		
		if (empty($address)) {
			return $this->outMessage($title, "", '-50', "缺少必填参数address");
		}
		
		$zip_code = isset($this->params['zip_code']) ? $this->params['zip_code'] : "";
		$alias = isset($this->params['alias']) ? $this->params['alias'] : "";
        $shipping_type = isset($this->params['shipping_type']) ? $this->params['shipping_type'] : "0";
        $shipping_company = isset($this->params['shipping_company']) ? $this->params['shipping_company'] : "0";
        $shipping_pick_up = isset($this->params['shipping_pick_up']) ? $this->params['shipping_pick_up'] : "0";
        $pick_up_id = 0;
        $shipping_company_id = 0;
        if ($shipping_type < 3) {
            //超商
            $pick_up_id = $shipping_pick_up;
        } else {
            $shipping_company_id = $shipping_company;
        }
		$data = array(
			'uid' => $this->uid,
			'consigner' => $consigner,
			'mobile' => $mobile,
			'phone' => $phone,
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'address' => $address,
			'zip_code' => $zip_code,
			'alias' => $alias,
            'pick_up_id' => $pick_up_id,
            'shipping_company_id' => $shipping_company_id
		);
		$retval = $applet_member->updateMemberExpressAddress($id, $data);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 获取用户地址详情
	 */
	public function addressDetail()
	{
		$title = "获取用户地址详情";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$address_id = isset($this->params['id']) ? $this->params['id'] : 0;
		
		$applet_member = new MemberService();
		$info = $applet_member->getMemberExpressAddressDetail($address_id);
		return $this->outMessage($title, $info);
	}
	
	/**
	 * 会员地址删除
	 */
	public function addressDelete()
	{
		$title = "删除会员地址";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$id = isset($this->params['id']) ? $this->params['id'] : "";
		$applet_member = new MemberService();
		$res = $applet_member->addressDelete($id);
		return $this->outMessage($title, $res, 0, getErrorInfo($res));
	}
	
	/**
	 * 修改会员默认地址
	 */
	public function modifyAddressDefault()
	{
		$title = "修改默认会员地址";
		$id = isset($this->params['id']) ? $this->params['id'] : 0;
		$applet_member = new MemberService();
		$res = $applet_member->updateAddressDefault($id);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 获取会员积分余额账户情况
	 */
	public function memberAccount()
	{
		$title = "获取会员积分余额账户情况";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$account_detail = $member->getMemberAccount($this->uid);
		return $this->outMessage($title, $account_detail);
	}
	
	/**
	 * 会员账户流水
	 */
	public function accountRecordsList()
	{
		$title = "获取会员账户流水,分为平台账户和店铺会员账户,余额只有平台账户account_type:1积分2余额";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		
		$account_type = $this->get('account_type', 1);
		$page_index = $this->get('page_index', 1);
		$page_size = $this->get('page_size', PAGESIZE);
		
		$condition['nmar.shop_id'] = 0;
		$condition['nmar.uid'] = $this->uid;
		$condition['nmar.account_type'] = $account_type;
		
		// 查看用户在该商铺下的积分消费流水
		$member = new MemberService();
		$account_records_list = $member->getAccountList($page_index, $page_size, $condition);
		return $this->outMessage($title, $account_records_list);
	}
	
	/**
	 * 会员优惠券
	 */
	public function coupon()
	{
		$title = "会员优惠券列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$type = isset($this->params['type']) ? $this->params['type'] : "";
		$counpon_list = $member->getMemberCounponList($type, $this->instance_id);
		foreach ($counpon_list as $key => $item) {
			$counpon_list[ $key ]['start_time'] = date("Y-m-d", $item['start_time']);
			$counpon_list[ $key ]['end_time'] = date("Y-m-d", $item['end_time']);
		}
		return $this->outMessage($title, $counpon_list);
	}
	
	/**
	 * 修改密码
	 */
	public function modifyPassword()
	{
		$title = "会员修改密码";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$old_password = isset($this->params['old_password']) ? $this->params['old_password'] : '';
		$new_password = isset($this->params['new_password']) ? $this->params['new_password'] : '';
		$retval = $this->checkPassword($new_password);
		$flag = $retval[0];
		if ($flag == 0) {
			$retval = $member->modifyUserPassword($this->uid, $old_password, $new_password);
			if ($retval == -2005) {
				return $this->outMessage($title, "", '-2005', "原始密码错误");
			}
		}
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 密码验证
	 */
	public function checkPassword($password)
	{
		$config = new ConfigService();
		$reg_config = $config->getRegisterAndVisitInfo(0);// 验证注册
		
		// 密码最小长度
		$min_length = $reg_config['pwd_len'];
		$password_len = strlen(trim($password));
		if ($password_len == 0) {
			return array(
				REGISTER_PASSWORD_ERROR,
				'密码不可为空'
			);
		}
		if ($min_length > $password_len) {
			return array(
				REGISTER_PASSWORD_ERROR,
				'密码最小长度为' . $min_length
			);
		}
		if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $password)) {
			return array(
				REGISTER_PASSWORD_ERROR,
				'密码格式错误'
			);
		}
		// 验证密码内容
		if (trim($reg_config['pwd_complexity']) != "") {
			if (stristr($reg_config['pwd_complexity'], "number") !== false) {
				if (!preg_match("/[0-9]/", $password)) {
					return array(
						REGISTER_PASSWORD_ERROR,
						'密码格式错误，密码中必须包含数字'
					);
				}
			}
			if (stristr($reg_config['pwd_complexity'], "letter") !== false) {
				if (!preg_match("/[a-z]/", $password)) {
					return array(
						REGISTER_PASSWORD_ERROR,
						'密码格式错误，密码中必须包含小写英文字母'
					);
				}
			}
			if (stristr($reg_config['pwd_complexity'], "upper_case") !== false) {
				if (!preg_match("/[A-Z]/", $password)) {
					return array(
						REGISTER_PASSWORD_ERROR,
						'密码格式错误，密码中必须包含大写英文字母'
					);
				}
			}
			if (stristr($reg_config['pwd_complexity'], "symbol") !== false) {
				if (!preg_match("/[^A-Za-z0-9]/", $password)) {
					return array(
						REGISTER_PASSWORD_ERROR,
						'密码格式错误，密码中必须包含符号'
					);
				}
			}
		} else {
			return array(
				0,
				''
			);
		}
	}
	
	/**
	 * 修改昵称
	 */
	public function modifyNickName()
	{
		$title = "会员修改昵称";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$nickname = isset($this->params['nickname']) ? $this->params['nickname'] : '';
		if (empty($nickname)) {
			return $this->outMessage($title, "", '-50', "无法获取昵称信息");
		}
		$member = new MemberService();
		$retval = $member->modifyNickName($this->uid, $nickname);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 积分兑换余额
	 */
	public function pointExchangeBalance()
	{
		$title = "积分兑换余额";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$point = isset($this->params['amount']) ? $this->params['amount'] : 0;
		$point = (float) $point;
		$result = [];
		if ($point > 0) {
			$member = new MemberService();
			$result = $member->memberPointToBalance($this->uid, $this->instance_id, $point);
		}
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 账户详情
	 */
	public function accountDetail()
	{
		$title = "会员银行账户详情";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$id = isset($this->params['id']) ? $this->params['id'] : 0;
		if (empty($id) || !is_numeric($id)) {
			return $this->outMessage($title, "", '-50', "无法获取账户详情");
		}
		$member = new MemberService();
		$account_info = $member->getMemberBankAccountDetail($id);
		return $this->outMessage($title, $account_info);
	}
	
	/**
	 * 账户列表
	 */
	public function accountQuery()
	{
		$title = "会员银行账户列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$account_list = $member->getMemberBankAccount();
		return $this->outMessage($title, $account_list);
	}
	
	/**
	 * 添加账户
	 */
	public function addAccount()
	{
		$title = "添加会员银行账户";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$realname = isset($this->params['realname']) ? $this->params['realname'] : "";
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : "";
		$account_type = isset($this->params['account_type']) ? $this->params['account_type'] : 1;
		$account_type_name = isset($this->params['account_type_name']) ? $this->params['account_type_name'] : "银行卡";
		$account_number = isset($this->params['account_number']) ? $this->params['account_number'] : "";
		$branch_bank_name = isset($this->params['branch_bank_name']) ? $this->params['branch_bank_name'] : "";
		if (!empty($account_type)) {
			if ($account_type == 2 || $account_type == 3) {
				if (empty($realname) || empty($mobile) || empty($account_type) || empty($account_type_name)) {
					return $this->outMessage($title, -1);
				}
			} else {
				if (empty($realname) || empty($mobile) || empty($account_type) || empty($account_type_name) || empty($account_number) || empty($branch_bank_name)) {
					return $this->outMessage($title, -2);
				}
			}
		} else {
			return $this->outMessage($title, -3);
		}
		if ($account_type == 2) {
			$user_service = new User();
			$user_info = $user_service->getUserInfo();
			if (!empty($user_info)) {
				$account_number = $user_info["wx_openid"];
			}
		}
		$data = array(
			'uid' => $this->uid,
			'account_type' => $account_type,
			'account_type_name' => $account_type_name,
			'branch_bank_name' => $branch_bank_name,
			'realname' => $realname,
			'account_number' => $account_number,
			'mobile' => $mobile,
			'create_date' => time(),
			'modify_date' => time()
		);
		$retval = $member->addMemberBankAccount($data);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 修改账户信息
	 */
	public function updateAccount()
	{
		$title = "修改账户信息";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$account_id = isset($this->params['id']) ? $this->params['id'] : '';
		$realname = isset($this->params['realname']) ? $this->params['realname'] : '';
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : '';
		$account_type = isset($this->params['account_type']) ? $this->params['account_type'] : '1';
		$account_type_name = isset($this->params['account_type_name']) ? $this->params['account_type_name'] : '银行卡';
		$account_number = isset($this->params['account_number']) ? $this->params['account_number'] : '';
		$branch_bank_name = isset($this->params['branch_bank_name']) ? $this->params['branch_bank_name'] : '';
		if (!empty($account_type)) {
			if ($account_type == 2 || $account_type == 3) {
				if (empty($realname) || empty($mobile) || empty($account_type) || empty($account_type_name)) {
					return $this->outMessage($title, -1);
				}
			} else {
				if (empty($realname) || empty($mobile) || empty($account_type) || empty($account_type_name) || empty($account_number) || empty($branch_bank_name)) {
					return $this->outMessage($title, -2);
				}
			}
		} else {
			return $this->outMessage($title, -3);
		}
		$data = array(
			'id' => $account_id,
			'account_type' => $account_type,
			'account_type_name' => $account_type_name,
			'branch_bank_name' => $branch_bank_name,
			'realname' => $realname,
			'account_number' => $account_number,
			'mobile' => $mobile,
			'modify_date' => time()
		);
		$retval = $member->updateMemberBankAccount($data);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 删除账户信息
	 */
	public function deleteAccount()
	{
		$title = "删除账户信息";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$applet_member = new MemberService();
		$account_id = isset($this->params['id']) ? $this->params['id'] : '';
		if (empty($account_id)) {
			return $this->outMessage($title, "", '-50', "无法获取账户信息");
		}
		$retval = $applet_member->delMemberBankAccount($account_id);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 设置默认账户
	 */
	public function modifyAccountDefault()
	{
		$title = "设置选中账户";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$account_id = isset($this->params['id']) ? $this->params['id'] : '';
		$retval = $member->setMemberBankAccountDefault($this->uid, $account_id);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 用户充值余额
	 */
	public function recharge()
	{
		$title = "用户充值余额";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$pay = new UnifyPay();
		$pay_no = $pay->createOutTradeNo();
		return $this->outMessage($title, $pay_no);
	}
	
	/**
	 * 创建充值订单
	 */
	public function createRechargeOrder()
	{
		$title = "创建充值订单";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$recharge_money = isset($this->params['recharge_money']) ? $this->params['recharge_money'] : 0;
		if ($recharge_money <= 0) {
			return $this->outMessage($title, "", '-50', "支付金额必须大于0");
		}
		$out_trade_no = isset($this->params['out_trade_no']) ? $this->params['out_trade_no'] : "";
		if (empty($out_trade_no)) {
			return $this->outMessage($title, "", '-50', "支付流水号不能为空");
		}
		$member = new MemberService();
		$retval = $member->createMemberRecharge($recharge_money, $this->uid, $out_trade_no);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 申请提现
	 */
	public function addWithdrawApply()
	{
		$title = "申请提现页面数据";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$bank_account_id = isset($this->params['bank_account_id']) ? $this->params['bank_account_id'] : '';
		if (empty($bank_account_id)) {
			return $this->outMessage($title, "", '-50', "无法获取账户信息");
		}
		$withdraw_no = time() . rand(111, 999);
		$cash = isset($this->params['cash']) ? $this->params['cash'] : '';
		$shop_id = 0;
		$member = new MemberService();
		$retval = $member->addMemberBalanceWithdraw($shop_id, $withdraw_no, $this->uid, $bank_account_id, $cash);
		return $this->outMessage($title, $retval, "", getErrorInfo($retval));
	}
	
	/**
	 * 更改用户头像
	 */
	public function modifyFace()
	{
		$title = '更换用户头像';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$user_headimg = isset($this->params['user_headimg']) ? $this->params['user_headimg'] : '';
		if (empty($user_headimg)) {
			return $this->outMessage($title, "", '-50', "无法获取用户头像信息");
		}
		$res = $member->modifyUserHeadimg($this->uid, $user_headimg);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 我的收藏
	 */
	public function collection()
	{
		$title = '我的收藏';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$type = isset($this->params['type']) ? $this->params['type'] : 0;
		$condition = array(
			"nmf.fav_type" => 'goods',
			"nmf.uid" => $this->uid
		);
		if ($type == 1) { // 获取本周内收藏的商品
			$start_time = mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
			$end_time = mktime(23, 59, 59, date("m"), date("d") - date("w") + 7, date("Y"));
			$condition["fav_time"] = array(
				"between",
				$start_time . "," . $end_time
			);
		} elseif ($type == 2) { // 获取本月内收藏的商品
			$start_time = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$end_time = mktime(23, 59, 59, date("m"), date("t"), date("Y"));
			$condition["fav_time"] = array(
				"between",
				$start_time . "," . $end_time
			);
		} elseif ($type == 3) { // 获取本年内收藏的商品
			$start_time = strtotime(date("Y", time()) . "-1" . "-1");
			$end_time = strtotime(date("Y", time()) . "-12" . "-31");
			$condition["fav_time"] = array(
				"between",
				$start_time . "," . $end_time
			);
		}
		
		$goods_collection_list = $member->getMemberGoodsFavoritesList($page_index, $page_size, $condition, "fav_time desc");
		foreach ($goods_collection_list['data'] as $k => $v) {
			if ($v['point_exchange_type'] == 0 || $v['point_exchange_type'] == 2) {
				$goods_collection_list['data'][ $k ]['display_price'] = '￥' . $v["promotion_price"];
			} else {
				if ($v['point_exchange_type'] == 1 && $v["promotion_price"] > 0) {
					$goods_collection_list['data'][ $k ]['display_price'] = '￥' . $v["promotion_price"] . '+' . $v["point_exchange"] . '积分';
				} else {
					$goods_collection_list['data'][ $k ]['display_price'] = $v["point_exchange"] . '积分';
				}
			}
			
			$v['fav_time'] = date("Y-m-d H:i:s", $v['fav_time']);
		}
		return $this->outMessage($title, $goods_collection_list);
	}
	
	/**
	 * 添加收藏
	 */
	public function addCollection()
	{
		$title = '添加收藏';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$fav_id = isset($this->params['fav_id']) ? $this->params['fav_id'] : '';
		$fav_type = isset($this->params['fav_type']) ? $this->params['fav_type'] : '';
		$log_msg = isset($this->params['log_msg']) ? $this->params['log_msg'] : '';
		$member = new MemberService();
		$result = $member->addMemberFavouites($fav_id, $fav_type, $log_msg);
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 取消收藏
	 */
	public function cancelCollection()
	{
		$title = '取消收藏';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$fav_id = isset($this->params['fav_id']) ? $this->params['fav_id'] : '';
		$fav_type = isset($this->params['fav_type']) ? $this->params['fav_type'] : '';
		$member = new MemberService();
		$result = $member->deleteMemberFavorites($fav_id, $fav_type);
		return $this->outMessage($title, $result);
	}
	
	/**
	 * 获取用户收藏商品数量
	 */
	public function collectionNum()
	{
		$title = '获取用户商品收藏数量';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$count = $member->getMemberGoodsCollectionNum($this->uid);
		return $this->outMessage($title, $count);
	}
	
	/**
	 * 我的足迹
	 */
	public function footprint()
	{
		$title = '我的足迹';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$good = new GoodsService();
		$data = $this->params;
		$page_index = isset($data['page_index']) ? $data['page_index'] : 1;
		$page_size = isset($data['page_size']) ? $data['page_size'] : PAGESIZE;
		$condition = [];
		$condition["uid"] = $this->uid;
		if (!empty($data['category_id']))
			$condition['category_id'] = $data['category_id'];
		
		$order = 'create_time desc';
		$list = $good->getGoodsBrowseList($page_index, $page_size, $condition, $order, $field = "*");
		foreach ($list['data'] as $key => $val) {
			$month = ltrim(date('m', $val['create_time']), '0');
			$day = ltrim(date('d', $val['create_time']), '0');
			$val['month'] = $month;
			$val['day'] = $day;
            $list['data'][$key]['display_promotion_price'] = bl_cf($val['promotion_price']);
		}
		
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 删除我的足迹
	 */
	public function deleteFootprint()
	{
		$title = '删除足迹';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$type = isset($this->params['type']) ? $this->params['type'] : '';
		$value = isset($this->params['value']) ? $this->params['value'] : '';
		
		if ($type == 'browse_id') $condition['browse_id'] = $value;
		if ($type == 'add_date') $condition['add_date'] = $value;
		
		if (empty($condition)) {
			return $this->outMessage($title, "", '-10', "删除失败，无法获取该足迹信息");
		}
		$good = new GoodsService();
		$res = $good->deleteGoodsBrowse($condition);
		
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 浏览记录
	 */
	public function browseRecord()
	{
		$title = '获取会员浏览记录';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$member = new MemberService();
		$data = $member->getMemberBrowseRecord($page_index, $page_size, [ 'uid' => $this->uid ], 'browse_id desc');
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 添加微信收货地址
	 */
	public function addWeixinAddress()
	{
		$title = '保存微信收货地址';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$address_service = new Address();
		$consigner = $this->get('consigner', '');
		$mobile = $this->get('mobile', '');
		$phone = $this->get('phone', '');
		$province = $this->get('province', '');
		$city = $this->get('city', '');
		$district = $this->get('district', '');
		$address = $this->get('address', '');
		$zip_code = $this->get('zip_code', '');
		$alias = $this->get('alias', '');
		$province = !empty($province) ? $address_service->getProvinceId($province)["province_id"] : "";
		$city = !empty($city) ? $address_service->getCityId($city)["city_id"] : "";
		$district = !empty($district) ? $address_service->getDistrictId($district)["district_id"] : "";
		$data = array(
			'uid' => $this->uid,
			'consigner' => $consigner,
			'mobile' => $mobile,
			'phone' => $phone,
			'province' => $province,
			'city' => $city,
			'district' => $district,
			'address' => $address,
			'zip_code' => $zip_code,
			'alias' => $alias,
			'is_default' => 0
		);
		$retval = $member->addMemberExpressAddress($data);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 浏览历史
	 */
	public function memberHistory()
	{
		$member = new MemberService();
		$member_historys = $member->getMemberViewHistory();
		return $this->outMessage("浏览历史", $member_historys);
	}
	
	/**
	 * 功能：删除浏览记录
	 */
	public function deleteMemberHistory()
	{
		$member = new MemberService();
		$member->deleteMemberViewHistory();
		return $this->outMessage('删除浏览记录', 1);
	}
	
	/**
	 * 获取会员优惠券数量
	 */
	public function couponNum()
	{
		$title = '获取会员优惠券数量';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$type = $this->get('type', 1);
		$num = $member->getUserCouponCount($type, 0);
		return $this->outMessage($title, $num);
	}
	
	/**
	 * 获取用户可领取的优惠券
	 */
	public function canReceiveCouponQuery()
	{
		$title = '获取用户可领取的优惠券';
		$uid = isset($this->params['uid']) ? $this->params['uid'] : "";
		$member = new MemberService();
		$res = $member->getMemberCouponTypeList($this->instance_id, $uid);
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 获取会员提现记录
	 */
	public function withdrawRecordList()
	{
		$title = '获取会员提现记录';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		
		$page = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$order = isset($this->params['order']) ? $this->params['order'] : '';
		
		// 该店铺下的余额提现记录
		$member = new MemberService();
		
		$condition['uid'] = $this->uid;
		$condition['shop_id'] = 0;
		
		$withdraw_list = $member->getMemberBalanceWithdraw($page, $page_size, $condition, $order);
		foreach ($withdraw_list['data'] as $k => $v) {
			if ($v['status'] == 1) {
				$withdraw_list['data'][ $k ]['status'] = '已同意';
			} else
				if ($v['status'] == 0) {
					$withdraw_list['data'][ $k ]['status'] = '已申请';
				} else {
					$withdraw_list['data'][ $k ]['status'] = '已拒绝';
				}
            $withdraw_list['data'][ $k ]['display_cash_price'] = bl_cf($v['cash']);
		}
		return $this->outMessage("", $withdraw_list);
	}
	
	/**
	 * 获取分享相关信息
	 * 首页、商品详情、推广二维码、店铺二维码
	 */
	public function shareContents()
	{
		$title = '获取分享相关信息';
		$flag = isset($this->params['flag']) ? $this->params['flag'] : 'shop';
		$goods_id = isset($this->params['goods_id']) ? $this->params['goods_id'] : '';
		$launch_id = isset($this->params['launch_id']) ? $this->params['launch_id'] : '';
		$article_id = isset($this->params['article_id']) ? $this->params['article_id'] : '';
		$group_id = isset($this->params['group_id']) ? $this->params['group_id'] : 0;
		
		$config_service = new ConfigService();
		$web_site = new WebSite();
		$web_info = $web_site->getWebSiteInfo();
		if (strstr($web_info['web_wechat_share_logo'], "http")) {
			$share_logo = $web_info['web_wechat_share_logo'];
		} else {
			$share_logo = Request::instance()->domain() . config('view_replace_str.__UPLOAD__') . '/' . $web_info['web_wechat_share_logo']; // 分享时，用到的logo，默认是平台分享图标
		}
		
		$shop = new ShopService();
		$config = $shop->getShopShareConfig();
		$use_wap_template = $config_service->getUseWapTemplate($this->instance_id);
		if (empty($use_wap_template)) {
			$use_wap_template['value'] = 'default';
		}
		
		// 当前用户名称
		$current_user = "";
		$share_url = "";
		
		$shop_name = $web_info['title'];
		$share_content = array();
		
		$user = new User();
		$user_info = null;
		if (!empty($this->uid)) {
			$user_info = $user->getUserInfoByUid($this->uid);
			$current_user = lang("分享人：") . $user_info["nick_name"];
		}
		
		switch ($flag) {
			case "goods":
				//商品
				if (!empty($this->uid)) {
					
					if (!empty($group_id)) {
						$share_url = __URL(__URL__ . '/wap/goods/detail?goods_id=' . $goods_id . '&group_id=' . $group_id . '&source_uid=' . $this->uid);
					} else {
						$share_url = __URL(__URL__ . '/wap/goods/detail?goods_id=' . $goods_id . '&source_uid=' . $this->uid);
					}
					
				} else {
					
					if (!empty($group_id)) {
						$share_url = __URL(__URL__ . '/wap/goods/detail?goods_id=' . $goods_id . '&group_id=' . $group_id);
					} else {
						$share_url = __URL(__URL__ . '/wap/goods/detail?goods_id=' . $goods_id);
					}
				}
				
				$goods = new GoodsService();
				$goods_detail = $goods->getGoodsDetail($goods_id);
				$share_content["share_title"] = $goods_detail["goods_name"];
				$share_content["share_contents"] = $config["goods_param_1"] . bl_cf($goods_detail["price"]) . ";" . $config["goods_param_2"];
				$share_content['share_nick_name'] = $current_user;
				if (count($goods_detail["img_list"]) > 0) {
					if (strstr($goods_detail["img_list"][0]["pic_cover_mid"], "http")) {
						$share_logo = $goods_detail["img_list"][0]["pic_cover_mid"];
					} else {
						$share_logo = Request::instance()->domain() . config('view_replace_str.__UPLOAD__') . '/' . $goods_detail["img_list"][0]["pic_cover_mid"]; // 用商品的第一个图片
					}
				}
				break;
			case "bargain":
				//砍价
				$share_url = __URL(__URL__ . '/wap/goods/bargainlaunch?launch_id=' . $launch_id. '&source_uid=' . $this->uid);
				$bargain_service = new Bargain();
				$launch_info = $bargain_service->getBargainLaunchInfo($launch_id);
				$bargain_goods = $bargain_service->getBargainGoodsInfo($launch_info['bargain_id'], $launch_info['goods_id']);
				$share_content["share_title"] = lang('我正在') . $launch_info['bargain_min_money'] . lang('元拿') . $bargain_goods["goods_name"] . lang('就差你一刀了');
				$share_content["share_contents"] = lang('原价：') . bl_cf($launch_info["goods_money"]) . ";";
				$share_content['share_nick_name'] = $current_user;
				$album = new Album();
				$picture_info = $album->getAlbumDetail($bargain_goods["goods_picture"]);
				if (!empty($picture_info['pic_cover_small'])) {
					$bargain_goods["goods_picture"] = $picture_info['pic_cover_small'];
				}
				if (strstr($bargain_goods["goods_picture"], "http")) {
					$share_logo = $bargain_goods["goods_picture"];
				} else {
					$share_logo = Request::instance()->domain() . config('view_replace_str.__UPLOAD__') . '/' . $bargain_goods["goods_picture"]; // 用商品的第一个图片
				}
				break;
			case "shop":
				//首页分享
				if (!empty($this->uid)) {
					$share_url = __URL(__URL__ . '/wap/index?source_uid=' . $this->uid);
				} else {
					$share_url = __URL(__URL__ . '/wap/index');
				}
				$share_content["share_title"] = $config["shop_param_1"] . $shop_name;
				$share_content["share_contents"] = $config["shop_param_2"] . " " . $config["shop_param_3"];
				$share_content['share_nick_name'] = $current_user;
				break;
			case "qrcode_shop":
				//店铺二维码
				if (!empty($this->uid)) {
					$share_url = __URL(__URL__ . '/wap/index/index?source_uid=' . $this->uid);
					$share_content["share_title"] = $config["shop_param_1"] . lang('分享'); // $shop_name . "二维码分享";
					$share_content["share_contents"] = $config["shop_param_2"] . ";" . $config["shop_param_3"];
					$share_content['share_nick_name'] = $current_user;
					if (!empty($user_info['user_headimg'])) {
						if (strstr($user_info['user_headimg'], "http")) {
							$share_logo = $user_info['user_headimg'];
						} else {
							$share_logo = Request::instance()->domain() . config('view_replace_str.__UPLOAD__') . '/' . $user_info['user_headimg'];
						}
					} else {
						$share_logo = Request::instance()->domain() . config('view_replace_str.__TEMP__') . '/wap/' . $use_wap_template['value'] . '/public/img/member_default.png';
					}
				}
				break;
			case "qrcode_my":
				//我的二维码
				if (!empty($this->uid)) {
					$share_url = __URL(__URL__ . '/wap/index/index?source_uid=' . $this->uid);
					$share_content["share_title"] = $shop_name . lang("二维码分享");
					$share_content["share_contents"] = $config["qrcode_param_1"] . ";" . $config["qrcode_param_2"];
					$share_content['share_nick_name'] = $current_user;
					if (!empty($user_info['user_headimg'])) {
						if (strstr($user_info['user_headimg'], "http")) {
							$share_logo = $user_info['user_headimg'];
						} else {
							$share_logo = Request::instance()->domain() . config('view_replace_str.__UPLOAD__') . '/' . $user_info['user_headimg'];
						}
					} else {
						$share_logo = Request::instance()->domain() . config('view_replace_str.__TEMP__') . '/wap/' . $use_wap_template['value'] . '/public/img/member_default.png';
					}
				}
				break;
			case "fx_shop_qrcode":
				//分销店铺二维码
				if (!empty($this->uid)) {
					$share_url = __URL(__URL__ . '/wap/index/shopindex?source_uid=' . $this->uid);
					$promoter = new NfxPromoter();
					$promoter_info = $promoter->getPromoterDetailByUid($this->uid);
					$share_content["share_title"] = $promoter_info['promoter_shop_name'] . lang('的店铺');
					$share_content["share_contents"] = lang('快来查看') . $promoter_info['promoter_shop_name'] . lang('的店铺');
					$share_content['share_nick_name'] = $current_user;
					if (!empty($user_info['user_headimg'])) {
						if (strstr($user_info['user_headimg'], "http")) {
							$share_logo = $user_info['user_headimg'];
						} else {
							$share_logo = Request::instance()->domain() . config('view_replace_str.__UPLOAD__') . '/' . $user_info['user_headimg'];
						}
					} else {
						$share_logo = Request::instance()->domain() . config('view_replace_str.__TEMP__') . '/wap/' . $use_wap_template['value'] . '/public/img/member_default.png';
					}
				}
				break;
			case "cms":
				//文章
				$share_url = __URL(__URL__ . '/wap/article/detail?article_id=' . $article_id);
				$article = new Article();
				$article_info = $article->getArticleDetail($article_id);
				$share_content["share_title"] = $article_info['short_title'];
				$share_content["share_contents"] = $article_info['summary'];
				$share_content['share_nick_name'] = $current_user;
				if (strstr($article_info["image"], "http")) {
					$share_logo = $article_info["image"];
				} else {
					$share_logo = Request::instance()->domain() . config('view_replace_str.__UPLOAD__') . '/' . $article_info["image"]; // 用商品的第一个图片
				}
				break;
		}
		$share_content["share_url"] = $share_url;
		$share_content["share_img"] = $share_logo;
		return $this->outMessage($title, $share_content);
	}
	
	/**
	 * 我的中奖记录
	 */
	public function winningRecordQuery()
	{
		$title = '我的中奖记录';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$promotion = new PromotionService();
		$condition = [
			"np_pgwr.uid" => $this->uid,
			"np_pgwr.shop_id" => 0,
			"np_pgwr.is_winning" => 1
		];
		$gamesWinningRecordsList = $promotion->getUserPromotionGamesWinningRecords(1, 0, $condition);
		return $this->outMessage($title, $gamesWinningRecordsList);
	}
	
	/**
	 * 退出登录
	 */
	public function logOut()
	{
		$title = '退出登录';
		$member = new MemberService();
		$member->Logout();
		if (isWeixin()){
		    Session::set('wchat_autologin_lock', true);
		}
		return $this->outMessage($title, 1);
	}
	
	/**
	 * 修改手机
	 */
	public function modifyMobile()
	{
		$title = '修改手机';
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		
		$member = new MemberService();
		
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : '';
		$sms_captcha = isset($this->params['code']) ? $this->params['code'] : "";
		
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		if (!$mobile) {
			return $this->outMessage($title, "", -1, "手机号错误");
		}
		
		$is_bin_mobile = $member->memberIsMobile($mobile);
		if ($is_bin_mobile) {
			return $this->outMessage($title, "", -1, "该手机号已存在");
		}
		
		// 是否需要验证短信验证码
		$web_config = new ConfigService();
		$noticeMobile = $web_config->getNoticeMobileConfig(0);
		$mobile_is_use = $noticeMobile[0]['is_use'];
		if ($mobile_is_use == 1) {
			$sms_captcha_code = Session::get('VerificationCode');
			//$sendMobile = Session::get('bindMobile');
			if ($sms_captcha != $sms_captcha_code || empty($sms_captcha_code)) {
				return $this->outMessage($title, "", -1, "动态码错误");
			}
		}
		
		$uid = $this->uid;
		$retval = $member->modifyMobile($uid, $mobile);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 绑定时发送短信验证码或邮件验证码
	 * @return number[]|string[]|string|mixed
	 */
	public function sendBindCode()
	{
		$title = '绑定时发送短信验证码或邮件验证码';
		$params['email'] = isset($this->params['email']) ? $this->params['email'] : '';
		$params['mobile'] = isset($this->params['mobile']) ? $this->params['mobile'] : '';
		$params['user_id'] = $this->uid;
		$type = isset($this->params['type']) ? $this->params['type'] : '';
		
		$params['shop_id'] = 0;
		if ($type == 'email') {
//			$hook = runhook('Notify', 'bindEmail', $params);
            $hook = message("bind_email", $params);
		} elseif ($type == 'mobile') {
//			$hook = runhook('Notify', 'bindMobile', $params);
            $hook = message("bind_mobile", $params);
		}
		if ($hook['code'] < 0) {
			$result = [
				'code' => -1,
				'message' => $hook['message']
			];
		} else {
			$result = [
				'code' => 0,
				'message' => '发送成功'
			];
			Session::set('VerificationCode', $hook['param']);
		}
		return $this->outMessage($title, $result);
		
	}
	
	/**
	 * 修改qq
	 */
	public function modifyQQ()
	{
		$title = '修改qq';
		$qq = isset($this->params['qq']) ? $this->params['qq'] : '';
		$member = new MemberService();
		$retval = $member->modifyQQ($this->uid, $qq);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 修改邮箱
	 */
	public function modifyemail()
	{
		$title = '修改邮箱';
		$member = new MemberService();
		
		$email = isset($this->params['email']) ? $this->params['email'] : '';
		$code = isset($this->params['code']) ? $this->params['code'] : "";
		
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		if (!$email) {
			return $this->outMessage($title, "", -1, "邮箱错误");
		}
		
		$is_bin_email = $member->memberIsEmail($email);
		if ($is_bin_email) {
			return $this->outMessage($title, "", -1, "该邮箱已存在");
		}
		
		// 是否需要验证短信验证码
		$web_config = new ConfigService();
		$noticeEmail = $web_config->getNoticeEmailConfig(0);
		$email_is_use = $noticeEmail[0]['is_use'];
		if ($email_is_use == 1) {

			$email_captcha_code = Session::get('VerificationCode');
			//$sendEmail = Session::get('bindEmail');
			if ($code != $email_captcha_code || empty($email_captcha_code)) {
				return $this->outMessage($title, "", -1, "动态码错误");
			}
		}
		
		$retval = $member->modifyEmail($this->uid, $email);
		return $this->outMessage($title, $retval);
	}
	
	/**
	 * 默认收货地址
	 */
	public function defaultAddress()
	{
		$title = '默认收货地址';
		$member = new MemberService();
		$info = $member->getDefaultExpressAddress();
		return $this->outMessage($title, $info);
	}
	
	/**
	 * 会员余额
	 */
	public function memberBalance()
	{
		$members = new MemberAccountService();
		$account = $members->getMemberBalance($this->uid);
		return $this->outMessage('会员余额', $account);
	}
	
	/**
	 * 默认账户
	 */
	public function defaultAccount()
	{
		$title = "会员银行账户列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, "", '-9999', "无法获取会员登录信息");
		}
		$is_default = $this->get('is_default', 1);
		$member = new MemberService();
		$account_list = $member->getMemberBankAccount($is_default);
		return $this->outMessage($title, $account_list);
	}
	
	/**
	 * 解除QQ绑定
	 */
	public function removeBindQQ()
	{
		$member = new MemberService();
		$member->removeBindQQ();
		return $this->outMessage('解除QQ绑定', $_SESSION['bind_pre_url']);
	}
	
	/**
	 * 领取奖品
	 */
	public function achieveGift()
	{
		if (empty($this->uid)) {
			return $this->outMessage('', "", '-9999', "无法获取会员登录信息");
		}
		$member = new MemberService();
		$promotion = new PromotionService();
		$address = $member->getDefaultExpressAddress();
		$gift_records_id = isset($this->params['record_id']) ? $this->params['record_id'] : '';
		$buyer_message = isset($this->params['buyer_message']) ? $this->params['buyer_message'] : '';
		$mobile = isset($this->params['mobile']) ? $this->params['mobile'] : '';
		$data = array(
			"uid" => $this->uid,
			"gift_records_id" => $gift_records_id,
			"receiver_mobile" => $address['mobile'],
			"buyer_message" => $buyer_message,
			"receiver_province" => $address['province'],
			"receiver_city" => $address['city'],
			"receiver_district" => $address['district'],
			"receiver_address" => $address['address'],
			"receiver_zip" => $address['zip_code'], // varchar(6) NOT NULL DEFAULT '' COMMENT '收货人邮编',
			"receiver_name" => $address['consigner'], // varchar(50) NOT NULL DEFAULT '' COMMENT '收货人姓名',
			"fixed_telephone" => $address["phone"],
			"mobile" => $mobile
		);
		$res = $promotion->userAchieveGift($data);
		return $this->outMessage('奖品领取', $res);
	}
	
	/**
	 * 制作店铺二维码
	 */
	function showShopQecode()
	{
		$uid = $this->uid;
		if ($this->instance_id == 0) {
			$url = __URL(__URL__ . '/wap?source_uid=' . $uid);
		} else {
			$url = __URL(__URL__ . '/wap/index/shopindex?shop_id=' . $this->instance_id . '&source_uid=' . $uid);
		}
		// 查询并生成二维码
		
		$upload_path = "upload/qrcode/promote_qrcode/shop"; // 后台推广二维码模版
		if (!file_exists($upload_path)) {
			mkdir($upload_path, 0777, true);
		}
		$path = $upload_path . '/shop_' . $uid . '_' . $this->instance_id . '.png';
		if (!file_exists($path)) {
			getQRcode($url, $upload_path, "shop_" . $uid . '_' . $this->instance_id);
		}
		
		// 定义中继二维码地址
		$thumb_qrcode = $upload_path . '/thumb_shop_' . 'qrcode_' . $uid . '_' . $this->instance_id . '.png';
		$image = \think\Image::open($path);
		// 生成一个固定大小为360*360的缩略图并保存为thumb_....jpg
		$image->thumb(260, 260, \think\Image::THUMB_CENTER)->save($thumb_qrcode);
		// 背景图片
		$dst = "public/static/images/qrcode_bg/shop_qrcode_bg.png";
		
		// $dst = "http://pic107.nipic.com/file/20160819/22733065_150621981000_2.jpg";
		// 生成画布
		list ($max_width, $max_height) = getimagesize($dst);
		$dests = imagecreatetruecolor($max_width, $max_height);
		$dst_im = getImgCreateFrom($dst);
		// if (substr($dst, - 3) == 'png') {
		// $dst_im = imagecreatefrompng($dst);
		// } elseif (substr($dst, - 3) == 'jpg') {
		// $dst_im = imagecreatefromjpeg($dst);
		// }
		imagecopy($dests, $dst_im, 0, 0, 0, 0, $max_width, $max_height);
		imagedestroy($dst_im);
		// 并入二维码
		// $src_im = imagecreatefrompng($thumb_qrcode);
		$src_im = getImgCreateFrom($thumb_qrcode);
		$src_info = getimagesize($thumb_qrcode);
		imagecopy($dests, $src_im, "94px" * 2, "170px" * 2, 0, 0, $src_info[0], $src_info[1]);
		imagedestroy($src_im);
		// 获取所在店铺信息
		
		$web = new WebSite();
		$shop_info = $web->getWebDetail();
		$shop_logo = $shop_info["logo"];
		$shop_name = $shop_info["title"];
		$shop_phone = $shop_info["web_phone"];
		$live_store_address = $shop_info["web_address"];
		
		// logo
		if (!strstr($shop_logo, "http://") && !strstr($shop_logo, "https://")) {
			if (!file_exists($shop_logo)) {
				$shop_logo = "public/static/images/logo.png";
			}
		}
		// if (substr($shop_logo, - 3) == 'png') {
		// $src_im_2 = imagecreatefrompng($shop_logo);
		// } elseif (substr($shop_logo, - 3) == 'jpg') {
		// $src_im_2 = imagecreatefromjpeg($shop_logo);
		// }
		$src_im_2 = getImgCreateFrom($shop_logo);
		$src_info_2 = getimagesize($shop_logo);
		imagecopy($dests, $src_im_2, "10px" * 2, "380px" * 2, 0, 0, $src_info_2[0], $src_info_2[1]);
		imagedestroy($src_im_2);
		// 并入用户姓名
		$rgb = hColor2RGB("#333333");
		$bg = imagecolorallocate($dests, $rgb['r'], $rgb['g'], $rgb['b']);
		$name_top_size = "430px" * 2 + "23";
		@imagefttext($dests, 23, 0, "10px" * 2, $name_top_size, $bg, "public/static/font/Microsoft.ttf", "店铺名称：" . $shop_name);
		@imagefttext($dests, 23, 0, "10px" * 2, $name_top_size + 50, $bg, "public/static/font/Microsoft.ttf", "电话号码：" . $shop_phone);
		@imagefttext($dests, 23, 0, "10px" * 2, $name_top_size + 100, $bg, "public/static/font/Microsoft.ttf", "店铺地址：" . $live_store_address);
		header("Content-type: image/jpeg");
		$img_path = "upload/qrcode/promote_qrcode/shop_qrcode/";
		if (!file_exists($img_path)) {
			mkdir($img_path, 0777, true);
		}
		$img_path = $img_path . '/shopQrcode_' . $this->instance_id . '_' . $this->uid . '.jpg';
		imagejpeg($dests, $img_path);
		return $this->outMessage('店铺二维码', $img_path);
	}
	
	/**
	 * 获取会员的虚拟商品列表
	 */
	public function virtualGoodsList()
	{
		$title = "获取会员的虚拟商品列表";
		if (empty($this->uid)) {
			return $this->outMessage($title, '', '-9999', "当前未登录");
		}
		$virtualGoods = new GoodsService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition['nvg.buyer_id'] = $this->uid;
		$order = "create_time desc";
		$virtual_list = $virtualGoods->getVirtualGoodsList($page_index, $page_size, $condition, $order);
		
		return $this->outMessage($title, $virtual_list);
	}
	
	/**
	 * 修改会员的基础信息
	 */
	public function updateMemberInformation()
	{
		$title = "修改会员的基础信息";
		$user_name = isset($this->params['user_name']) ? $this->params['user_name'] : '';
		$user_qq = isset($this->params['user_qq']) ? $this->params['user_qq'] : '';
		$real_name = isset($this->params['real_name']) ? $this->params['real_name'] : '';
		$sex = isset($this->params['sex']) ? $this->params['sex'] : '';
		$location = isset($this->params['location']) ? $this->params['location'] : '';
		$birthday = isset($this->params['birthday']) ? $this->params['birthday'] : '';
		if (empty($birthday)) {
			$birthday = date('Y-m-d', strtotime($birthday));
		}
		
		$member = new MemberService();
		$res = $member->updateMemberInformation($user_name, $user_qq, $real_name, $sex, $birthday, $location, '');
		return $this->outMessage($title, $res);
	}
	
	/**
	 * 获取会员等级列表
	 */
	public function memberLevelQuery()
	{
		$title = "会员等级";
		$member = new MemberService();
		$list = $member->getMemberLevelList(1, 0, '', 'level asc');
		return $this->outMessage($title, $list);
	}
	
	/**
	 * 获取会员等级信息
	 */
	public function memberLevel()
	{
		$title = "获取会员等级信息";
		$level_id = $this->get('level_id', '');
		$member = new MemberService();
		$level_detail = $member->getMemberLevelDetail($level_id);
		return $this->outMessage($title, $level_detail);
	}
	
	/**
	 * 领取优惠券
	 */
	public function getCoupon()
	{
		$title = "领取优惠券";
		if (empty($this->uid)) {
			return $this->outMessage($title, '', '-9999', "当前未登录");
		}
		$coupon_type_id = isset($this->params['coupon_type_id']) ? $this->params['coupon_type_id'] : '';
		$scenario_type = isset($this->params['scenario_type']) ? $this->params['scenario_type'] : 2;
		if (empty($coupon_type_id)) {
			return $this->outMessage($title, '', '-50', "无法获取优惠券信息");
		}
		$member = new MemberService();
		$res = $member->memberGetCoupon($this->uid, $coupon_type_id, 3);
		return $this->outMessage($title, $res, $res, getErrorInfo($res));
	}
	
	/**
	 * 检测邮箱是否存在
	 */
	public function checkEmail()
	{
		$title = '判断邮箱是否存在';
		$user_email = isset($this->params['email']) ? $this->params['email'] : "";
		$member = new MemberService();
		$exist = $member->memberIsEmail($user_email);
		if ($exist) {
			return $this->outMessage($title, $exist, 0, '手机号已存在');
		} else {
			return $this->outMessage($title, $exist, 0, '手机号不存在');
		}
	}
	
	/**
	 * 检测手机号是否存在
	 */
	public function checkMobile()
	{
		$title = '判断手机号是否存在';
		$user_mobile = isset($this->params['mobile']) ? $this->params['mobile'] : "";
		$member = new MemberService();
		$exist = $member->memberIsMobile($user_mobile);
		if ($exist) {
			return $this->outMessage($title, $exist, 0, '手机号已存在');
		} else {
			return $this->outMessage($title, $exist, 0, '手机号不存在');
		}
	}
	
	/**
	 * 判断用户名是否存在
	 */
	public function checkUsername()
	{
		$title = '判断用户名是否存在';
		$username = $this->get('username', '');
		$member = new MemberService();
		$exist = $member->judgeUserNameIsExistence($username);
		if ($exist) {
			return $this->outMessage($title, $exist, 0, '用户名已存在');
		} else {
			return $this->outMessage($title, $exist, 0, '用户名不存在');
		}
	}
	
	/**
	 * 获取兑换比例
	 */
	public function exchangeRate()
	{
		$member_account = new MemberAccountService();
		$res = $member_account->getConvertRate($this->instance_id);
		return $this->outMessage("获取兑换比例", $res);
	}
	
	/**
	 * 获取会员订单数量
	 */
	public function memberOrderCount()
	{
		$title = "获取会员订单数量";
		if (empty($this->uid)) {
			return $this->outMessage($title, '', '-9999', "当前未登录");
		}
		$condition = [
			"buyer_id" => $this->uid
		];
		$order_query = new OrderQuery();
		$data = $order_query->getOrderStatusNum($condition);
		return $this->outMessage($title, $data);
	}
	
	/**
	 * 获取会员最近一条订单
	 */
	public function memberLastOrder()
	{
		$title = "获取会员最近的一条订单";
		if (empty($this->uid)) {
			return $this->outMessage($title, '', '-9999', "当前未登录");
		}
		$order_query = new OrderQuery();
		$last_order = $order_query->getOrderList(1, 1, [ "buyer_id" => $this->uid, 'order_status' => 4 ], 'order_id desc');
		if (!empty($last_order['data'])) {
			return $this->outMessage($title, $last_order['data'][0]);
		} else {
			return $this->outMessage($title, null);
		}
	}
	
	/**
	 * 获取会员消费情况
	 */
	public function memberConsum()
	{
		$title = "获取会员消费情况";
		if (empty($this->uid)) {
			return $this->outMessage($title, '', '-9999', "当前未登录");
		}
		
		$order_query = new OrderQuery();
		$condition = [
			"buyer_id" => $this->uid,
			"order_status" => 4
		];
		$data = [
			'order_money' => sprintf("%.2f", $order_query->getPayMoneySum($condition)),
			'order_count' => $order_query->getOrderCount($condition)
		];
		if (!empty($data['order_money']) && !empty($data['order_count'])) {
			$data['avg_order_money'] = sprintf("%.2f", ($data['order_money'] / $data['order_count']));
		} else {
			$data['avg_order_money'] = '0.00'; // 平均消费
		}
		return $this->outMessage($title, $data);
	}
	
}