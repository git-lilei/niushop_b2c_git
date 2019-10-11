/**
 * 后台新界面
 * 2017年7月26日 12:02:39
 */

$(function() {

	isShowAsideFooter();

	//控制操作提示按鈕是否显示
	if($(".right-side-operation li").length>1){
		$(".js-open-warmp-prompt").show();
	}

	//打开操作提示
	$(".js-open-warmp-prompt").click(function(){
		var is_visible = $(this).next().is(":visible");
		if(!is_visible) $(this).next().show();
		else $(this).next().hide();
		return false;
//		return;
//		var menu_desc = $(this).attr("data-menu-desc");
//		var html = '<div>';
//				html += "<h4>操作提示</h4>";
//				html += menu_desc;
//
//				html += '<h4>功能提示</h4>';
//				html += '<p>提示：如果，请查看是否是由于以下原因造成：1、是否添加了”模板消息“功能插件。添加方法：在微信公众平台的左侧菜单点击”添加功能插件“按钮，在右侧点击”模板消息“，再点击”申请“，填写”申请开通模板消息接口“表单，点击”提交“，等待审核通过即可。2、在微信公众平台的“模板库”里点击“修改行业”，弹出层里面的“主营行业”全部选择“消费品”，”副营行业“全部选择”其它“;3、在微信公众平台里”我的模板“最多添加15个，如果多于15个，请删除不用的模板。</p>';
//			html += '</div>';
//		$(".js-open-warmp-prompt").popover({ content : html });
//		$(".js-open-warmp-prompt").popover("show");
//		return;
//		setWarmPrompt("show",function(){
//			$(".ns-warm-prompt").show(400);
//			$(".js-open-warmp-prompt").parent().fadeOut(400);
//		});
	});

	//关闭操作提示
	$(".ns-warm-prompt .alert .close").click(function() {
		setWarmPrompt("hidden",function(){
			$(".ns-warm-prompt").hide(400);
			$(".js-open-warmp-prompt").parent().fadeIn(400);
		});
		return false;
	});

})
window.onresize = function() {

	isShowAsideFooter();

};

// 控制左侧边栏的底部是否显示
function isShowAsideFooter() {
	if ($(".ns-base-aside nav li").length >= 20) {
		$(".ns-base-aside footer").hide();
	} else if ($(window).height() <= 530) {
		$(".ns-base-aside footer").hide();
	} else if ($(".ns-base-aside nav li").length > 8 && $(window).height() < 820) {
		$(".ns-base-aside footer").hide();
	}else if ($(".ns-base-aside nav li").length > 5 && $(window).height() < 700) {
		$(".ns-base-aside footer").hide();
	} else {
		$(".ns-base-aside footer").show();
	}
}

// 设置是否显示提示
function setWarmPrompt(value,fn){
	$.ajax({
		type : 'post',
		url : __URL(ADMINMAIN + "/Index/setWarmPromptIsShow"),
		data : { "value" : value },
		success : function(res){
			if(fn != undefined){
				fn.call(this);
			}
		}
	});
}
