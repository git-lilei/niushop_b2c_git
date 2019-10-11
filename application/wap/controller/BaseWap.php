<?php
/**
 * BaseWap.php
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

use data\extend\WchatOauth;
use data\service\Config as WebConfig;
use data\service\Member;
use data\service\promotion\PromoteRewardRule;
use data\service\WebSite;
use think\Controller;
use think\Cookie;
use think\Session;


class BaseWap extends Controller
{
    
    public $user;
    
    protected $uid;
    
    protected $instance_id;
    
    public $web_info;
    
    public $style;
    
    // 自定义模板开启状态 0 不启用 1 启用
    public $custom_template_is_enable;
    
    public $use_wap_template;
    
    //加密后的uid
    public $token;
    
    //会员基础信息
    public $member_detail;
    
    public function __construct()
    {
        Cookie::delete("default_client"); // 还原手机端访问
        
        // getWapCache();//开启缓存
        parent::__construct();
        $this->initInfo();
    }
    
    public function initInfo()
    {
        
        $this->user = new Member();
        $web_site = new WebSite();
        $config = new WebConfig();
        
        $this->web_info = $web_site->getWebSiteInfo();
        
        // wap端关闭后
        if ($this->web_info['wap_status'] == 3 && $this->web_info['web_status'] == 1) {
            Cookie::set("default_client", "web");
            $controller = request()->controller();
            $action = request()->action();
            if ($controller == "Goods" && $action == "detail") {
                $goods_id = request()->get("id", 0);
                $this->redirect(__URL(\think\Config::get('view_replace_str.SHOP_MAIN') . "/web/goods/detail?goods_id=" . $goods_id));
            } else {
                $this->redirect(__URL(\think\Config::get('view_replace_str.SHOP_MAIN') . "/web"));
            }
        } elseif ($this->web_info['wap_status'] == 2) {
            webClose($this->web_info['close_reason']);
        } elseif (($this->web_info['wap_status'] == 3 && $this->web_info['web_status'] == 3) || ($this->web_info['wap_status'] == 3 && $this->web_info['web_status'] == 2)) {
            webClose($this->web_info['close_reason']);
        }
        
        $this->token = session("niu_access_token");
        $this->member_detail = session("niu_member_detail");
        
        //TODO: 增加自动注册用户
        if (empty($this->token) || empty($this->member_detail)) {
            //自动注册改用cookie作为登录判断
            $this->token = Cookie::get('niu_access_token');
            $this->member_detail = Cookie::get("niu_member_detail");
            if (empty($this->token) ||empty($this->member_detail)) {
                //注册
                $this->autoRegister();
            }
        } else if (!Cookie::get('niu_access_token') || !Cookie::get("niu_member_detail")) {
            Cookie::set('niu_access_token', $this->token);
            Cookie::set('niu_member_detail', $this->member_detail);
        }
        
        
        if (!empty($this->member_detail)) {
            $this->uid = $this->member_detail['user_info']['uid'];
            $this->assign("uid", $this->uid);
        } else {
            $this->assign("uid", '');
        }
        
        $this->instance_id = 0;
        $this->assign("member_detail", $this->member_detail);
        $this->assign('page_size', PAGESIZE);
        
        $sign_package = api("System.Wchat.getShareTicket");
        $sign_package = $sign_package['data'];
        $this->assign("signPackage", $sign_package);
        
        //SEO搜索引擎
        $seo = api("System.Config.seo");
        $seo = $seo['data'];
        $this->assign("seo_config", $seo);
        
        $color_scheme = api("System.Config.webSiteColorScheme", ['flag' => 'wap']);
        $color_scheme = $color_scheme['data'];
        $theme_css = "theme.css";
        if (!empty($color_scheme)) {
            $theme_css = $color_scheme['file_name'];
        }
        $this->assign("theme_css", $theme_css);
        
        // 手机端自定义模板是否开启标识
//		$this->custom_template_is_enable = 0;
//		if (addon_is_exit('NsDiyView')) {
//			$this->custom_template_is_enable = $config->getIsEnableWapCustomTemplate($this->instance_id);
//		}
//
//		$this->assign("custom_template_is_enable", $this->custom_template_is_enable);
        
        // 手机端自定义模板底部菜单
//		$this->getWapCustomTemplateFooter();
        
        // 使用那个手机模板
        $this->use_wap_template = $config->getUseWapTemplate($this->instance_id);
        
        if (empty($this->use_wap_template)) {
            $this->use_wap_template['value'] = 'default';
        }
        // 检查模版是否存在
        if (!checkTemplateIsExists("wap", $this->use_wap_template['value'])) {
            $this->error("模板配置有误，请联系商城管理员");
        }
        
        $default_images = api("System.Config.defaultImages");
        $default_images = $default_images['data'];
        $this->assign("default_goods_img", $default_images["value"]["default_goods_img"]); // 默认商品图片
        $this->assign("default_headimg", $default_images["value"]["default_headimg"]); // 默认用户头像
        $this->assign("default_cms_thumbnail", $default_images["value"]["default_cms_thumbnail"]); // 默认文章缩略图
        
        $this->style = "wap/" . $this->use_wap_template['value'] . "/";
        $this->assign("style", "wap/" . $this->use_wap_template['value']);
        $this->assign("base", "wap/" . $this->use_wap_template['value'] . '/base');
        $this->assign("goods_detail_base", "wap/" . $this->use_wap_template['value'] . '/goods/detail');
        
        //获取当前控制器方法
        $controller = request()->controller();
        $action = request()->action();
        $this->assign('action', $action);
        $this->assign('controller', $controller);
        if (!request()->isAjax()) {
            if (!Session::get('wchat_autologin_lock') && isWeixin()){
                $wchat_config = $config->getInstanceWchatConfig(0);
                // 在没有配置微信公众号的情况下，微信浏览器内仍能正常访问，
                if (!empty($wchat_config['value']['appid']) && !empty($wchat_config['value']['appsecret'])) {
                    if (empty($this->uid)) {
                        $this->wchatLogin();
                    }
                }
            }
            $this->assign("instance_id", $this->instance_id);
            $this->assign("title", $this->web_info['title']);//网站标题
            $this->assign('title_before', $this->web_info['title']);
            $this->assign("web_info", $this->web_info);
            $this->assign("platform_shop_name", $this->user->getInstanceName()); // 平台店铺名称
            $this->assign("title_before", '');
            $this->assign('page_size', PAGESIZE);
            
        }
        
        $source_uid = request()->get('source_uid', '');
        if ($source_uid && addon_is_exit('Nsfx')) {
            $promote = new PromoteRewardRule();
            $source_info = $promote->getPromoterDetailByUid($source_uid);
            $this->assign('title_before', $source_info['promoter_shop_name'] . '的店铺');
        }
    }
    
    public function _empty($name)
    {
    }
    
    protected function autoRegister()
    {
        $member = new Member();
        $user_name = 'u'.round(microtime(true)*100,0);
        $password = time();
        $email = '';
        $mobile = '';
        $retval_id = $member->registerMember($user_name, $password, $email, $mobile, '', '', '', '', '');
        if ($retval_id > 0) {
            Session::pull('mobileVerificationCode');
            session::pull('mobileVerificationCode_time');
            $data = $member->getMemberLoginInfo();
            session('niu_access_token', $data['token']);
            Cookie::set('niu_access_token', $data['token']);
            
            $this->uid = $member->getSessionUid();
            $this->instance_id = $member->getSessionInstanceId();
            $member_detail = api("System.Member.memberInfo");
    
            session("niu_member_detail", $member_detail['data']);
            Cookie::set('niu_member_detail', $member_detail['data']);
    
            $this->token = $data['token'];
            $this->member_detail = $member_detail;
        }
        return $member;
    }
    /**
     * 微信自动登录
     */
    public function wchatLogin()
    {
        // 如果是微信浏览器
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
            $webconfig = new WebConfig();
            $register_config = $webconfig->getRegisterAndVisitInfo($this->instance_id);
            $domain_name = \think\Request::instance()->domain();
            $session_token = Session::get($domain_name . "member_access_token");
            if (empty($session_token)) {
                // 获取token
                $wchat_oauth = new WchatOauth();
                $token = $wchat_oauth->get_member_access_token();
                
                if (!empty($token['openid'])) {
                    // 针对分销版的处理
                    if (addon_is_exit('Nsfx')) {
                        $source_uid = request()->get('source_uid', '');
                        if (!empty($source_uid)) {
                            $_SESSION['source_uid'] = $source_uid;
                        }
                    }
                    
                    // 如果存在unionid
                    if (!empty($token['unionid'])) {
                        $wx_unionid = $token['unionid'];
                        $retval = $this->user->wchatUnionLogin($wx_unionid);
                        if ($retval == 1) {
                            $this->user->refreshUserOpenid($token['openid'], $wx_unionid);
                            return;
                        } elseif ($retval == USER_LOCK) {
                            $redirect = __URL(__URL__ . "/wap/login/lock");
                            $this->redirect($redirect);
                        }
                    }
                    // 如果unionid登录失败
                    $info = $wchat_oauth->get_oauth_member_info($token);
                    $retval = $this->user->wchatLogin($token['openid']);
                    if ($retval == USER_NBUND && $register_config["is_requiretel"] == 0) {
                        $this->user->registerMember('', '123456', '', '', '', '', $token['openid'], $info, $token['unionid']);
                        return;
                    } elseif ($retval == USER_LOCK) {
                        // 锁定跳转
                        $redirect = __URL(__URL__ . "/wap/login/lock");
                        $this->redirect($redirect);
                    }
                    $token['info'] = $info;
                    Session::set($domain_name . "member_access_token", json_encode($token));
                    
                } else {
                    die("微信无法获取token，请检测微信配置！");
                }
            }
            
        }
    }
    
    protected function view($template = '', $vars = [], $replace = [], $code = 200)
    {
        $view_replace_str = [
            'WAP_CSS'    => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/css',
            'WAP_FONT'   => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/font',
            'WAP_JS'     => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/js',
            'WAP_IMG'    => __ROOT__ . '/template/wap/' . $this->use_wap_template['value'] . '/public/img',
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
     * js中调用api
     * @return mixed
     */
    public function ajaxApi()
    {
        $method = input("method", "");
        $param = input("param", "");
        if (empty($method)) {
            return [
                'title'   => "javascript调用api",
                'data'    => "",
                'code'    => -400,
                'message' => "接口发生错误：method is not empty",
            ];
        }
        if (!empty($param)) {
            $param = json_decode($param, true);
        }
        $res = api($method, $param);
        return $res;
    }
    
    /**
     * 语言包接口
     */
    public function langApi()
    {
        $data = input("data", "");
        if (!empty($data)) {
            $data = explode(",", $data);
            //键值反转
            $data = array_flip($data);
            foreach ($data as $k => $v) {
                $data[$k] = lang($k);
            }
        }
        return $data;
    }
    
    /**
     * 手机端base.html公用底部菜单
     */
    public function getWapCustomTemplateFooter()
    {
        $config = new WebConfig();
        $teplate_info = $config->getFormatCustomTemplate();
        if (empty($teplate_info)) {
            $this->custom_template_is_enable = 0;//如果开启了自定义模板，但是没有内容，应该跳转到普通首页
        }
        $custom_footer = array();
        if (!empty($teplate_info)) {
            $custom_template_info = $teplate_info['template_data'];
            foreach ($custom_template_info as $k => $v) {
                $custom_template_info[$k]["style_data"] = json_decode($v["control_data"], true);
            }
            for ($i = 0; $i < count($custom_template_info); $i++) {
                $v = $custom_template_info[$i];
                if ($v["control_name"] == "Footer") {
                    // 首页
                    if (trim($v["style_data"]["footer"]) != "") {
                        // 底部菜单
                        $custom_footer = json_decode($v["style_data"]["footer"], true);
                        break;
                    }
                }
            }
        }
        
        // 当前打开页面时，底部菜单的的对应的选中
        $select_footer_index = 0; // 底部菜单下标标识
        $template_id = request()->get('id', 0);
        if (!empty($custom_footer) && (substr(request()->pathinfo(), -34, -1) != 'CustomTemplate/customTemplateInde' || $template_id != 0)) {
            foreach ($custom_footer as $k => $v) {
                // 如果只有wap，没有index/index
                if (strpos(strtolower(request()->module() . "/" . request()->action()), strtolower(request()->pathinfo())) !== false) {
                    $select_footer_index = 0;
                    break;
                }
                if (strpos(strtolower($v['href']), strtolower(request()->pathinfo())) !== false) {
                    $select_footer_index = $k;
                }
            }
        }
        $this->assign("select_footer_index", $select_footer_index);
        $this->assign("custom_footer", $custom_footer);
    }
    
    /**
     * 检测用户
     */
    protected function checkLogin()
    {
        $redirect = __URL(__URL__ . "/wap/login");
        if (empty($this->token)) {
            $_SESSION['login_pre_url'] = __URL(__URL__ . $_SERVER['PATH_INFO']);
            $this->redirect($redirect);
        }
    }
    
    /**
     * 是否已登录
     */
    protected function logined()
    {
        return empty($this->token) ? false : true;
    }
    
    public function warning()
    {
        return $this->view($this->style . 'warning');
    }
}