<?php
/**
 * TempMsg.php
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
namespace addons\NsWxtemplatemsg\admin\controller;

use app\admin\controller\BaseController;
use addons\NsWxtemplatemsg\data\service\WeixinTemplate;
use data\service\Member;

/**
 * 模板消息控制器
 */
class TempMsg extends BaseController
{

    public $addon_view_path;
    
    public function __construct()
    {
        parent::__construct();
        $this->addon_view_path = ADDON_DIR . '/NsWxtemplatemsg/template/';
    }
    
    public function index(){
         $template_service = new WeixinTemplate();
         $list = $template_service->getList();
         $this->assign("list", $list);
         return view($this->addon_view_path.$this->style ."TempMsg/index.html");
    }
    
    /**
     * 获取模板id
     */
    public function getTemplateId()
    {
       $template_service = new WeixinTemplate();
       $res = $template_service->getTemplateId();
        return AjaxReturn($res);
    }
    
    /**
     * 设置模板消息是否启用
     */
    public function changeIsEnable()
    {
        $id = request()->post('id', 0);
        $is_enable = request()->post('is_enable', 0);
        $template_service = new WeixinTemplate();
        $res = $template_service->changeIsEnable($id, $is_enable);
        return AjaxReturn($res);
    }
    
    /**
     * 清空模板id
     */
    public function emptyTemplateId()
    {
       $template_service = new WeixinTemplate();
       $res = $template_service->emptyTemplateId();
        return AjaxReturn($res);
    }

    /**
     * 模板消息配置
     */
    public function config(){
        $template_service = new WeixinTemplate();
        if (request()->isAjax()) {
            $value = request()->post("value");
            $is_use = request()->post("is_use");
            $data = array(
                "value" => $value,
                "is_use" => $is_use,
            );
            $retval = $template_service->setTemplateConfig($data);
            return AjaxReturn($retval);
        }else{
            $value = $template_service->getTemplateConfig();
            $this->assign("config", $value);
            return view($this->addon_view_path.$this->style ."TempMsg/config.html");
        }
    }

    /**
     * 获取用户列表
     */
    public function getMemberList()
    {
        if (request()->isAjax()) {
            $search_text = request()->post("search_text", "");
            $member = new Member();
            $condition = [
                'su.is_member' => 1,
                'su.nick_name|su.user_tel|su.user_email' => array(
                    'like',
                    '%' . $search_text . '%'
                ),
                'su.is_system' => 0,
                'su.wx_openid' => ["neq", ""]
            ];
            $list = $member->getMemberList(1, 0, $condition);
            return $list;
        }
    }
    
    
    /**
     * 重置模板消息
     */
    public function resetTemplate(){
        $template_service = new WeixinTemplate();
        $res = $template_service->resetWxTemplate();
        if($res["code"] > 0){
            return AjaxReturn(1);
        }else{
            return AjaxReturn(-1);
        }
        
    }

}