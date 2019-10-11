<?php
/**
 * Article.php
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
/**
 * 文章
 */
class Article extends BaseWap
{
	
	/**
	 * 首页
	 */
	public function lists()
	{
		$this->assign("title_before", "文章中心");
		$this->assign("title", lang('article_center'));
		$config_service = new Config();
		$result = $config_service->getDefaultImages($this->instance_id);
		$this->assign("article_img", $result['value']['default_cms_thumbnail']);
		$this->assign("info", $result);
		return $this->view($this->style . 'article/lists');
	}
	
	/**
	 * 文章内容
	 */
	public function detail()
	{
		$article_id = request()->get('article_id', '');
		$this->assign("article_id", $article_id);
		
		$article_info = api("System.Article.articleInfo", [ "article_id" => $article_id ]);
		$article_info = $article_info['data'];
		$this->assign("article_info", $article_info);
		
		$condition = [];
		$condition['article_id'] = [ 'gt', $article_id ];
		$condition['status'] = 2;
		
		//上一篇
		$prev_info = api("System.Article.articleFirst", [ "condition" => $condition, "order" => "public_time asc" ]);
		
		//下一篇
		$condition['article_id'] = [ 'lt', $article_id ];
		$next_info = api("System.Article.articleFirst", [ "condition" => $condition, "order" => "public_time desc" ]);
		
		$this->assign("prev_info", $prev_info['data']);
		$this->assign("next_info", $next_info['data']);
		
		$this->assign("title_before", $article_info['title'] ? $article_info['title'] : "文章详情");
		$this->assign("title", $article_info['title'] ? $article_info['title'] : "文章详情");
		
		return $this->view($this->style . 'article/detail');
	}
}