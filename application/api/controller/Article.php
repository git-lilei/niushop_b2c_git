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

namespace app\api\controller;

use data\service\Article as ArticleService;

class Article extends BaseApi
{
	
	/**
	 * 文章列表
	 */
	public function articleList()
	{
		$article = new ArticleService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : PAGESIZE;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : "";
        $order = isset($this->params['order']) ? $this->params['order'] : "public_time desc";
        if (! empty($condition) && is_string($condition) && json_decode($condition)) {
            $condition = json_decode($condition, true);
        }
        $result = $article->getArticleList($page_index, $page_size, $condition, $order);
		return $this->outMessage("", $result);
	}
	
	/**
	 * 文章详情
	 */
	public function articleInfo()
	{
		$article = new ArticleService();
		$article_id = isset($this->params['article_id']) ? $this->params['article_id'] : 0;
		$info = $article->getArticleDetail($article_id);
		if (!empty($info)) {
			$info["content"] = htmlspecialchars_decode(html_entity_decode($info["content"], ENT_COMPAT, "UTF-8"), ENT_COMPAT);
		}
		return $this->outMessage("文章详情", $info);
	}
	
	/**
	 * 获取文章分类
	 */
	public function articleClassList()
	{
		$article = new ArticleService();
		$page_index = isset($this->params['page_index']) ? $this->params['page_index'] : 1;
		$page_size = isset($this->params['page_size']) ? $this->params['page_size'] : 0;
		$condition = isset($this->params['condition']) ? $this->params['condition'] : "";
        $order = isset($this->params['order']) ? $this->params['order'] : "sort desc";
        if (! empty($condition) && is_string($condition) && json_decode($condition)) {
            $condition = json_decode($condition, true);
        }
        $list = $article->getArticleClass($page_index, $page_size, $condition, $order);
		return $this->outMessage("获取文章分类", $list);
	}
	
	/**
	 * 获取专题详情
	 */
	public function topicInfo()
	{
		$article = new ArticleService();
		$topic_id = isset($this->params['topic_id']) ? $this->params['topic_id'] : "";
		$res = $article->getTopicDetail($topic_id);
		return $this->outMessage("获取专题详情", $res);
	}
	
	/**
	 * 最*的一条数据
	 */
	public function articleFirst()
	{
		$order = isset($this->params['order']) ? $this->params['order'] : '';
		$condition = isset($this->params['condition']) ? $this->params['condition'] : "";
		if (! empty($condition) && is_string($condition) && json_decode($condition)) {
            $condition = json_decode($condition, true);
        }
		$article = new ArticleService();
		$data = $article->getArticleFirst($condition, $order);
		return $this->outMessage("获取文章", $data);
	}
	
	/**
	 * 获取文章分类信息
	 */
	public function articleClassInfo()
	{
		$article = new ArticleService();
		$class_id = isset($this->params['class_id']) ? $this->params['class_id'] : "";
		$field = isset($this->params['field']) ? $this->params['field'] : "*";
		$data = $article->getArticleClassDetail($class_id,$field);
		return $this->outMessage('获取文章分类信息', $data);
	}
}