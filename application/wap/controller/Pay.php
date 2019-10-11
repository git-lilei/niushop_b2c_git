<?php
/**
 * Pay.php
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

use data\service\Config;
use think\Controller;

/**
 * 支付控制器
 */
class Pay extends Controller
{
	
	public $shop_config;
	public $style;
	public $use_wap_template;
	
	public function __construct()
	{
		parent::__construct();
		
		$website_config_result = api('System.Config.webSite', []);
		$website = $website_config_result["data"];
		$this->assign("web_info", $website);
		$this->assign("title", $website['title']);
		
		//seo配置
		$seo_config_result = api('System.Config.seo', []);
		$seo_config = $seo_config_result["data"];
		$this->assign("seoconfig", $seo_config);
		
		// 购物设置
		$shop_config_result = api('System.Config.trade', []);
		$this->shop_config = $shop_config_result["data"];
		$this->assign("shop_config", $this->shop_config);
		
		$unpaid_goback = "";
		if (isset($_SERVER['HTTP_REFERER'])) {
			if (strpos($_SERVER['HTTP_REFERER'], "paymentorder")) {
				// 如果上一个界面是待付款订单，则直接返回当前的订单详情
				$unpaid_goback = isset($_SESSION['unpaid_goback']) ? $_SESSION['unpaid_goback'] : '';
			} else {
				$unpaid_goback = $_SERVER['HTTP_REFERER'];
			}
		}
		$this->assign("unpaid_goback", $unpaid_goback); // 返回到订单
		
		$config = new Config();
		$this->use_wap_template = $config->getUseWapTemplate(0);
		
		if (empty($this->use_wap_template)) {
			$this->use_wap_template['value'] = 'default';
		}
		
		$color_scheme = api("System.Config.webSiteColorScheme",['flag'=>'wap']);
		$color_scheme = $color_scheme['data'];
		$theme_css = "theme.css";
		if(!empty($color_scheme)){
			$theme_css = $color_scheme['file_name'];
		}
		$this->assign("theme_css", $theme_css);
		$this->style = "wap/" . $this->use_wap_template['value'] . "/";
		$this->assign("style", "wap/" . $this->use_wap_template['value']);
		$this->assign("base", "wap/" . $this->use_wap_template['value'] . '/base');
		
	}
	
	protected function view($template = '', $vars = [], $replace = [], $code = 200)
	{
		$view_replace_str = [
			'WAP_CSS' => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/css',
			'WAP_JS' => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/js',
			'WAP_IMG' => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/img',
			'WAP_PLUGIN' => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/plugin',
		];
		
		if (empty($replace)) {
			$replace = $view_replace_str;
		} else {
			$replace = array_merge($view_replace_str, $replace);
		}
		return view($template, $vars, $replace, $code);
	}
	
	/**
	 * 获取支付相关信息
	 */
	public function getPayValue()
	{
		$out_trade_no = request()->get('out_trade_no', '');
		if (empty($out_trade_no)) {
			$this->error("没有获取到支付信息");
		}
		$pay_info = api('System.Pay.getPayValue', [ 'out_trade_no' => $out_trade_no ]);
		if (empty($pay_info['data'])) {
			$this->error($pay_info['message'], __URL(__URL__));
		}
		$pay_value = $pay_info['data']['pay_value'];
		$zero1 = time(); // 当前时间 ,注意H 是24小时 h是12小时
		$zero2 = $pay_value['create_time'];
		
		if ($pay_value['pay_status'] != 0) {
		    // 订单已经支付
		    $this->error("订单已经支付或者订单价格为0.00，无需再次支付!", __URL(__URL__ . "wap/member/index"));
		}
		if ($zero1 > ($zero2 + ($this->shop_config['order_buy_close_time'] * 60))) {
			$this->error("订单已关闭");
		} else {
			$this->assign('pay_value', $pay_value);
			if (request()->isMobile()) {
				$this->assign("title", '订单支付');
				$this->assign("title_before", '订单支付');
				return $this->view($this->style . 'pay/info_wap'); // 手机端
			} else {
				return $this->view($this->style . 'pay/info_pc'); // PC端
			}
		}
	}
	
	/**
	 * 在线支付
	 */
	public function onlinePay()
	{
		$type = request()->get("type", "");
		$out_trade_no = request()->get('no', '');
		if (empty($type)) {
			$this->error("没有获取到支付方式");
		}
		if (!is_numeric($out_trade_no)) {
			$this->error("没有获取到支付信息");
		}
		$base_url = str_replace("/index.php", "", __URL__);
		$base_url = str_replace("index.php", "", $base_url);
		$notify_url = $base_url . "/pay.php";
		$return_url = __URL(__URL__ . '/wap/Pay/payReturn');
		//返回形式  code_url, url , 直接跳转
		$result = hook("pay", [ 'addon_name' => $type, 'out_trade_no' => $out_trade_no, 'notify_url' => $notify_url, 'return_url' => $return_url ]);
		$result = arrayFilter($result);
		$result = $result[0];
		
		if ($result['data']['return_type'] == 'qrcode') {
			$path = getQRcode($result['data']['code_url'], "upload/qrcode/pay", $out_trade_no);
			$this->assign("path", __ROOT__ . '/' . $path);
			
			$pay_result = api('System.Pay.payInfo', [ 'out_trade_no' => $out_trade_no ]);
			$pay_value = $pay_result["data"];
			
			$this->assign('pay_value', $pay_value);
			return $this->view($this->style . "pay/qrcode");
			
		} elseif ($result['data']['return_type'] == 'url') {
			if ($result['code'] > 0) {
				$this->redirect($result['data']['url']);
			} else {
				$this->error($result['data']['out_message']);
			}
			
		} elseif ($result['data']['return_type'] == 'html') {
			echo $result['data']['html'];
		}
	}
	
	/**
	 * 同步回调
	 */
	public function payReturn()
	{
		$out_trade_no = request()->get('out_trade_no', ''); // 流水号
		$result = hook("payReturn", []);
		$result = arrayFilter($result);
		$success = 0;
		foreach ($result as $k => $v) {
			if ($v["data"]["msg"] == 1) {
				$success = 1;
			}
		}
		//只要存在 验证成功的支付方式就可以通过
		if ($success == 1) {
			$msg = 1;
		} else {
			$pay_result = api('System.Pay.payInfo', [ 'out_trade_no' => $out_trade_no ]);
			$pay_info = $pay_result["data"];
			if ($pay_info['pay_status'] == 1) {
				$msg = 1;
			} else {
				$msg = 2;
			}
		}
		$this->assign("status", $msg);
		$order_no_result = api('System.Pay.getOrderNoByOutTradeNo', [ 'out_trade_no' => $out_trade_no ]);
		$order_no = $order_no_result["data"]["order_no"];
		
		$this->assign("order_no", $order_no);
		if (request()->isMobile()) {
			return $this->view($this->style . "pay/callback_wap");
		} else {
			return $this->view($this->style . "pay/callback_pc");
		}
	}
	
	/**
	 * 支付异步回调
	 */
	public function payNotify()
	{
		hook('payNotify', []);
	}
	
	/**
	 * 支付状态
	 */
	public function payStatus()
	{
		if (request()->isAjax()) {
			$out_trade_no = request()->post("out_trade_no", "");
			$pay_result = api('System.Pay.payInfo', [ 'out_trade_no' => $out_trade_no ]);
			$pay_info = $pay_result["data"];
			if ($pay_info['pay_status'] > 0) {
				return $retval = array(
					"code" => 1,
					"message" => ''
				);
			} else {
				return $retval = array(
					"code" => 0,
					"message" => ''
				);
			}
		}
	}
	
	/**
	 * 余额支付选择界面
	 */
	public function pay()
	{
		if (request()->isAjax()) {
			$out_trade_no = request()->post("out_trade_no", 0);
			$is_use_balance = request()->post("is_use_balance", 0);
			$pay_result = api('System.Pay.orderBindBalance', [ 'out_trade_no' => $out_trade_no, 'is_use_balance' => $is_use_balance ]);
			$res = $pay_result["data"];
			return $res;
		} else {
			
			$out_trade_no = request()->get("out_trade_no", 0);
			// 支付信息
			$pay_result = api('System.Pay.payInfo', [ 'out_trade_no' => $out_trade_no ]);
			$pay_value = $pay_result["data"];
			
			$this->assign("pay_value", $pay_value);
			if (empty($out_trade_no) || !is_numeric($out_trade_no) || empty($pay_value)) {
				$this->error("没有获取到支付信息");
			}
//            if (empty($uid)) {
//                $this->error("没有获取到用户信息");
//            }
			
			//余额交易配置
			$balance_config_result = api('System.Config.balancePay', []);
			$balance_config = $balance_config_result["data"];
			
			// 如果商家未开启余额支付或余额未0 直接跳转到支付界面
			if ($balance_config['value'] == 0) {
				$this->redirect(__URL(__URL__ . "wap/pay/getPayValue?out_trade_no=" . $out_trade_no));
			}
			
			// 此次交易最大可用余额
			$max_balance_result = api('System.Pay.maxPayBalance', [ "out_trade_no" => $out_trade_no ]);
			$member_balance = $max_balance_result["data"]["balance"];
			$this->assign("member_balance", $member_balance);
			if ($member_balance == 0) {
				$this->redirect(__URL(__URL__ . "wap/pay/getPayValue?out_trade_no=" . $out_trade_no));
			}
			//支付方式
			$pay_config_result = api('System.Pay.getPayConfig', []);
			$pay_config = $pay_config_result["data"];
			$this->assign("pay_config", $pay_config);
			//获取订单状态
			$order_status_result = api('System.Pay.getOrderStatusByOutTradeNo', [ "out_trade_no" => $out_trade_no ]);
			$order_status = $order_status_result["data"]["order_status"];
			
			// 订单关闭状态下是不能继续支付的
			if ($order_status == 5) {
				$this->error("订单已关闭");
			}
			// 还需支付的金额
			$need_pay_money = round($pay_value['pay_money'], 2) - round($member_balance, 2);
			$this->assign("need_pay_money", sprintf("%.2f", $need_pay_money));
			
			$zero1 = time(); // 当前时间 ,注意H 是24小时 h是12小时
			$zero2 = $pay_value['create_time'];
			
			if ($zero1 > ($zero2 + ($this->shop_config['order_buy_close_time'] * 60))) {
				$this->error("订单已关闭");
			} else {
				if (request()->isMobile()) {
					$this->assign("title", '订单支付');
					return $this->view($this->style . 'pay/wap_pay');
				} else {
					return $this->view($this->style . "pay/pc_pay");
				}
			}
		}
	}
}