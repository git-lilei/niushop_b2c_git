<?php
/**
 * Index.php
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

use think\Controller;
class Index extends Controller
{
    /**
     * 当前版本的路径
     */
    public function __construct()
    {
        parent::__construct();
    
    }
    /*
	 * api首页
	 */
	public function index()
	{
	    $params = input();
	    if(!isset($params['method']))
	    {
	        echo json_encode(AjaxReturn('', 'PARAMETER_ERROR'));exit();
	    }
	    
	    $method_array = explode('.', $params['method']);
	    if ($method_array[0] == 'System') {
	        $class_name = 'app\\api\\controller\\' . $method_array[1];
	        if (!class_exists($class_name)) {
	             echo json_encode(AjaxReturn('', 'PARAMETER_ERROR'));exit();
	        }
	        $api_model = new $class_name($params);
	    } else {
	    
	        $class_name = "addons\\{$method_array[0]}\\api\\controller\\" . $method_array[1];
	        if (!class_exists($class_name)) {
	             echo json_encode(AjaxReturn('', 'PARAMETER_ERROR'));exit();
	        }
	        $api_model = new $class_name($params);
	    }
	    $function = $method_array[2];
	    $data = $api_model->$function($params);
	    echo json_encode($data, JSON_UNESCAPED_UNICODE);exit();
	}
}