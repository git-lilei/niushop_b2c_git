<?php
/**
 * WeixinTemplate.php
 *
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

namespace addons\NsWxtemplatemsg\data\service;

use data\service\BaseService;
use data\extend\WchatOauth;
use addons\NsWxtemplatemsg\data\model\SysAddonsWeixinTemplateMsgModel;
use data\model\ConfigModel;


/**
 * 微信模板消息
 */
class WeixinTemplate extends BaseService
{
	
	/**
	 * 获取模板消息设置列表
	 */
	public function getList($condition = [])
	{
		$weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
		$list = $weixin_template_model->getQuery($condition);
		return $list;
	}
	
	/**
	 * 获取模板消息设置列表
	 */
	public function getInfo($condition)
	{
		$weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
		$info = $weixin_template_model->getInfo($condition);
		return $info;
	}
	
	/**
	 * 根据模板编号 获取 模板id
	 */
	public function getTemplateIdByTemplateNo($template_no)
	{
		$wchat = new WchatOauth();
		$json = $wchat->templateID($template_no);
		$array = json_decode($json, true);
		$template_id = '';
		if ($array) {
			$template_id = $array['template_id'];
		}
		return $template_id;
	}
	
	/**
	 * 设置模板消息是否启用
	 */
	public function changeIsEnable($id, $is_enable)
	{
		$weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
		$res = $weixin_template_model->save([ 'is_enable' => $is_enable ], [ 'id' => $id ]);
		return $res;
	}
	
	/**
	 * 更新模板消息
	 */
	public function updateTemplate($data, $condition)
	{
		$weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
		$res = $weixin_template_model->save($data, $condition);
		return $res;
	}
	
	/**
	 * 获取模板id
	 * @return boolean|number
	 */
	public function emptyTemplateId()
	{
		$weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
		$res = $weixin_template_model->save([ 'template_id' => '' ], [ 'instance_id' => $this->instance_id ]);
		return $res;
	}
	
	/**
	 * 获取模板id
	 */
	public function getTemplateId()
	{
		$weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
		$condition['template_id'] = '';
		$list = $weixin_template_model->getQuery($condition);
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$template_id = $this->getTemplateIdByTemplateNo($v['template_no']);
				if ($template_id) {
					$weixin_template_model->save([
						'template_id' => $template_id
					], [ "id" => $v['id'] ]);
				}
			}
		}
		return 1;
	}
	
	/**
	 * 模板消息配置
	 * @param $param
	 */
	public function getTemplateConfig(){
	    $config_model = new ConfigModel();
	    $condition = array(
	        "key" => "WX_TEMPLATE_MSG"
	    );
	    $info = $config_model->getInfo($condition);
	    if(empty($info)){
	        $data = array(
	            "key" => "WX_TEMPLATE_MSG",
	            "value" => "",
	            "desc" => "微信消息模板配置",
	            "create_time" => time(),
	        );
	        $result = $config_model->save($data);
	        $info = $config_model->getInfo($condition);
	    }
	    return $info;
	}
	
	/**
	 * 配置模板消息
	 * @param $data
	 */
	public function setTemplateConfig($data){
	    $config_model = new ConfigModel();
	    $condition = array(
	        "key" =>  "WX_TEMPLATE_MSG"
	    );
	    $retval = $config_model->save($data, $condition);
	    return $retval;
	}
	
	/**
	 * 重置模板消息
	 */
	public function resetWxTemplate(){
	    //批量删除原来的所有模板消息
	    $weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
	    $weixin_template_model->startTrans();
	    try{
    	    $wchat = new WchatOauth();
    	    $template_json = $wchat->getAllPrivateTemplate();
    	    $template_list = json_decode($template_json, true);
    	    if(!empty($template_list["template_list"])){
    	        foreach($template_list["template_list"] as $list_k => $list_v){
    	            $result = $wchat->delPrivateTemplate($list_v["template_id"]);
    	        }
    	    }
    	    
    	    $list = $weixin_template_model->getQuery([], "template_no", "", "template_no");
    	    foreach($list as $k => $v){
    	        $result_json = $wchat->templateID($v["template_no"]);
    	        $result = $array = json_decode($result_json, true);
    	        if($result["errcode"] != 0){
    	            $weixin_template_model->rollback();
    	            return error($result);
    	        }
    	        $weixin_template_model = new SysAddonsWeixinTemplateMsgModel();
    	        $weixin_template_model->save(["template_id" => $result["template_id"]], ["template_no" => $v["template_no"]]);
    	    }
    	    $weixin_template_model->commit();
    	    return success();
	    }catch (\Exception $e){
	        $weixin_template_model->rollback();
	        return error($e->getMessage());
	    }
	    
	}
}
