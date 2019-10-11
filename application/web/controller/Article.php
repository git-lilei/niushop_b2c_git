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

namespace app\web\controller;

/**
 * 文章控制器
 */
class Article extends BaseWeb
{
	/**
	 * 文章列表
	 */
	public function lists()
	{
		$page_index = request()->get('page', 1);
		$class_id = request()->get('class_id', '');
		$condition = [];
		$condition['status'] = 2;
		if (!empty($class_id)) {
			$condition['nca.class_id'] = $class_id;
		}
		
		$article_class_info = api("System.Article.articleClassInfo", ['class_id' => $class_id,'field'=>'name']);
		$article_class_info = $article_class_info['data'];
		
		$this->assign('page_index', $page_index);
		$this->assign('class_id', $class_id);
		$this->assign('condition', $condition);
		
		if(empty($article_class_info)){
            $this->assign("title_before", "文章列表");
		}else{
		    $this->assign("title_before", $article_class_info['name']);
		}
		return $this->view($this->style . 'article/lists');
	}
	
	/**
	 * 文章详情
	 */
	public function detail($article_id = '')
	{
		if (empty($article_id)) {
			$article_id = request()->get('article_id', 0);
		}
		
		if (empty($article_id)) {
			$this->error("文章信息不存在");
		}
		
		$this->assign("article_id", $article_id);
		
		//文章分类列表
		$class_list = api("System.Article.articleClassList");
		
		$info = api("System.Article.articleInfo", [ "article_id" => $article_id ]);
		$info = $info['data'];
		
		$condition = [];
		$condition['article_id'] = [ 'gt', $article_id ];
		$condition['status'] = 2;
		
		//上一篇
		$prev_info = api("System.Article.articleFirst", [ "condition" => $condition, "order" => "public_time asc" ]);
		
		//下一篇
		$condition['article_id'] = [ 'lt', $article_id ];
		$next_info = api("System.Article.articleFirst", [ "condition" => $condition, "order" => "public_time desc" ]);
		
		$this->assign("info", $info);
		$this->assign("class_id", $info['class_id']);
		$this->assign("prev_info", $prev_info['data']);
		$this->assign("next_info", $next_info['data']);
		$this->assign("class_list", $class_list['data']['data']);
		
		$this->assign("title_before", $info['title'] ? $info['title'] : "文章详情");
		return $this->view($this->style . 'article/detail');
	}
}