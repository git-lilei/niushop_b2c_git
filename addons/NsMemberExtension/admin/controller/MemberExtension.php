<?php
/**
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

namespace addons\NsMemberExtension\admin\controller;

use app\admin\controller\BaseController;
use data\service\Weixin;

/**
 * 会员推广
 */
class MemberExtension extends BaseController
{
	
	public $addon_view_path;
	
	public function __construct()
	{
		parent::__construct();
		$this->addon_view_path = ADDON_DIR . '/NsMemberExtension/template/';
	}
	
	/**
	 * 微信推广模板设置
	 * @return \think\response\View
	 */
	public function index()
	{
	    $weixin = new Weixin();
	    $type = request()->get('type', 1);
	    $template_list = $weixin->getWeixinQrcodeTemplate($this->instance_id, $type);
	    $this->assign("template_list", $template_list);
	    $this->assign("type", $type);
		return view($this->addon_view_path . $this->style . 'MemberExtension/index.html');
	}
	
	/**
	 * 添加或编辑推广模板
	 */
	public function qrcodeTemplate(){
	    $weixin = new Weixin();
	    $web_info = $this->website->getWebSiteInfo();
	    if (request()->isAjax()) {
	        $id = request()->post('id', 0);
	        $background = request()->post('background', '');
	        $nick_font_color = request()->post('nick_font_color', '#000');
	        $nick_font_size = request()->post('nick_font_size', '12');
	        $is_logo_show = request()->post('is_logo_show', '1');
	        $header_left = request()->post('header_left', '59');
	        $header_top = request()->post('header_top', '15');
	        $name_left = request()->post('name_left', '128');
	        $name_top = request()->post('name_top', '23');
	        $logo_left = request()->post('logo_left', '60');
	        $logo_top = request()->post('logo_top', '200');
	        $code_left = request()->post('code_left', '70');
	        $code_top = request()->post('code_top', '300');
	        $qrcode_type = request()->post('qrcode_type', 1);
	        $upload_path = "upload/qrcode/promote_qrcode_template"; // 后台推广二维码模版
	        $template_url = $upload_path . '/qrcode_template_' . $id . '_' . $this->instance_id . '.png';
	        	
	        $data = array(
	            'instance_id' => $this->instance_id,
	            'background' => $background,
	            'nick_font_color' => $nick_font_color,
	            'nick_font_size' => $nick_font_size,
	            'is_logo_show' => $is_logo_show,
	            'header_left' => $header_left . 'px',
	            'header_top' => $header_top . 'px',
	            'name_left' => $name_left . 'px',
	            'name_top' => $name_top . 'px',
	            'logo_left' => $logo_left . 'px',
	            'logo_top' => $logo_top . 'px',
	            'code_left' => $code_left . 'px',
	            'code_top' => $code_top . 'px',
	            'template_url' => $template_url,
	            'qrcode_type' => $qrcode_type
	        );
	        if ($id == 0) {
	            $res = $weixin->addWeixinQrcodeTemplate($data);
	            showUserQecode($upload_path, '', $upload_path . '/thumb_template_' . 'qrcode_' . $res . '_' . $this->instance_id . '.png', '', $web_info['logo'], '', request()->post(), $upload_path . '/qrcode_template_' . $res . '_' . $this->instance_id . '.png');
	            $data['id'] = $res;
	            $data['template_url'] = $upload_path . '/qrcode_template_' . $res . '_' . $this->instance_id . '.png';
	            $res = $weixin->updateWeixinQrcodeTemplate($data);
	        } else {
	            $data['id'] = $id;
	            $res = $weixin->updateWeixinQrcodeTemplate($data);
	            showUserQecode($upload_path, '', $upload_path . '/thumb_template_' . 'qrcode_' . $id . '_' . $this->instance_id . '.png', '', $web_info['logo'], '', request()->post(), $upload_path . '/qrcode_template_' . $id . '_' . $this->instance_id . '.png');
	        }
	        return AjaxReturn($res);
	    } else {
	        $id = request()->get('id', 0);
	        if (empty($id)) {
	            $info = $weixin->getDetailWeixinQrcodeTemplate(0);
	        } else {
	            $info = $weixin->getDetailWeixinQrcodeTemplate($id);
	        }
	        	
	        $this->assign('id', $id);
	        $this->assign('type', request()->get('type', 1));
	        $this->assign("info", $info);
	        $this->assign('web_info', $web_info);
	        return view($this->addon_view_path . $this->style . 'MemberExtension/qrcodeTemplate.html');
	    }
	}
	
	/**
	 * 设置为默认
	 */
	public function qrcodeTemplateSetDefault(){
	    $id = request()->post('id', '');
	    $type = request()->post('type', '');
	    $weixin = new Weixin();
	    $retval = $weixin->modifyWeixinQrcodeTemplateCheck($this->instance_id, $id, $type);
	    return AjaxReturn($retval);
	}
	
	/**
	 * 删除模板
	 */
	public function deleteQrcodeTemplate(){
	    $id = request()->post('id', '');
	    $weixin = new Weixin();
	    $retval = $weixin->deleteWeixinQrcodeTemplate($id, $this->instance_id);
	    return AjaxReturn($retval);
	}
}