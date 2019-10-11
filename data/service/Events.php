<?php
/**
 * Events.php
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

namespace data\service;

use data\model\NsGoodsPromotionModel;
use data\model\SysCronModel;

/**
 * 计划任务
 */
class Events
{
	/**
	 * 执行计划任务
	 */
	public function cronExecute()
	{
		$sys_cron_model = new SysCronModel();
		$list = $sys_cron_model->getQuery([ 'status' => 1 ]);
		$time = time();
		
		foreach ($list as $k => $v) {
			
			//查询周期时间
			$time_space = $v['cron_period'] * $v['cron_time'] * 60;
			if ($time_space > $time - $v['last_time']) {
				continue;
			}
			try {
				if (empty($v['cron_addon'])) {
					$class_name = $v['cron_class_name'];
					$class = new $class_name();
					$function = $v['cron_function'];
					$res = $class->$function();
					
				} elseif (empty($v['cron_hook'])) {
					$addon_is_exit = addon_is_exit($v['cron_addon']);
					if ($addon_is_exit) {
						$class_name = $v['cron_class_name'];
						$class = new $class_name();
						$function = $v['cron_function'];
						$res = $class->$function();
					} else {
						$res = 0;
					}
				} else {
					$res = hook($v['cron_hook'], [ 'addon_name' => $v['cron_addon'] ]);
					$res = arrayFilter($res);
					$res = $res[0];
				}
				if (is_array($res)) {
					$res = json_encode($res);
				}
				if (!isset($res)) {
					$res = 1;
				}
			} catch (\Exception $e) {
				$res = $e->getMessage();
			}
			
			$sys_cron_model = new SysCronModel();
			$sys_cron_model->save([ 'last_time' => time(), 'cron_result' => $res ], [ 'cron_id' => $v['cron_id'] ]);
		}
	}
	
	public function goodsPromotion()
	{
		$goods_promotion_model = new NsGoodsPromotionModel();
		$condition_start = array(
			'start_time' => array( 'ELT', time() ),
			'status' => 0
		);
		$goods_promotion_model->save([ 'status' => 1 ], $condition_start);
		$goods_promotion_model->where('end_time != 0 AND end_time < ' . time())->delete();
	}
	
	/**
	 * 清除商品海报
	 */
    public function clearGoodsPoster(){
	    $retval = NiuDelDir(ROOT_PATH.'upload/goods_poster');
	    return $retval;
	}
}