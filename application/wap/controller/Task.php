<?php
/**
 * Task.php
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

use data\service\Events;
use think\Controller;
use think\Log;

\think\Loader::addNamespace('data', 'data/');

/**
 * 执行定时任务
 */
class Task extends Controller
{
	/**
	 * 加载执行任务
	 */
	public function load_task()
	{
		$redirect = __URL(__URL__ . "/wap/task/event");
		http($redirect, $timeout = 1);
		return 1;
	}
	
	/**
	 * 自动执行任务
	 */
	public function event()
	{
		$last_time = cache("last_load_time");
		if ($last_time == false) {
			$last_time = 0;
		}
		ignore_user_abort();
		set_time_limit(0);
		cache("task_load", 1);
		do {
			$task_load = cache("task_load");
			if ($task_load == false) {
				Log::write("清除缓存，可能进行了系统更新，跳出循环");
				break;//跳出循环
			}
			$last_time = cache("last_load_time");
			if ($last_time == false) {
				$last_time = 0;
			}
			$time = time();
			if (($time - $last_time) < 30) {
				Log::write("跳出多余循环项，保证当前只存在一个循环");
				break;//跳出循环
			}
			cache("last_load_time", time());
			$event = new Events();
			$event->cronExecute();
			Log::write("检测循环");
			cache("last_load_time", time());
			sleep(60);
		} while (TRUE);
	}
}