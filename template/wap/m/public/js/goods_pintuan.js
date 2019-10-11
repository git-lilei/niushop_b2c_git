$(function(){
	getPageList(1);
})
var is_load = false;
function getPageList(page){
	//设置选中效果
	$("#page").val(page);//当前页
	if(is_load){
		return false;
	}
	is_load = true;
	var condition = {
		'npg.is_open' : 1
	};
	api('NsPintuan.Pintuan.goodsList', {"page_index":page, condition : JSON.stringify(condition)}, function(res) {
		var data = res.data;
		$("#page_count").val(data['page_count']);//总页数
		is_load = false;
		if(page == 1){
			var html = '';
		}else if(page > 1){
			var html = $('.spelling-block').html();
		}
		if(data['data'].length==0){
			html += '<div class="empty"><img src="'+WAPIMG+'/wap_nodata.png"/><p class="ns-text-color-gray">沒有找到您想要的拼團商品…</p></div>';
		}else{
			html += '<ul>';
			for(var i=0;i<data['data'].length;i++){
				var curr = data['data'][i];

				html += `<li onclick="location.href='`+ __URL(APPMAIN + '/goods/detail?goods_id=' + curr.goods_id) +`'">
                        <div>
                            <img src="`+ __IMG(curr.pic_cover_mid) +`" class="lazy_load pic">
                        </div>
                        <footer>
                            <p class="ns-text-color-black">`+ curr.goods_name +`</p>
                            <div class="assemble-tag">
                            	<div class="already-num ns-text-color ns-bg-color-fadeout-80">已搶`+ curr.sales +`件</div>
                            	<div class="people-num ns-text-color ns-border-color-fadeout-80">`+ curr.tuangou_num +`人團</div>`;
					        	if(curr.shipping_fee == 0){
					        		html += '<div class="people-num ns-text-color ns-border-color-fadeout-80">包邮</div>';
					        	}
	                    html += `</div>
	                    <div class="assemble-foot">
	                        <div class="assemble-foot-left">
	                        	 <div class="tuangou-money ns-text-color">NT$`+ curr.tuangou_money +`</div>
	                            <div class="original-money ns-text-color-gray">單買價`+ curr.promotion_price +`</div>
	                        </div>
	                       <div class="assemble-foot-right">
	                        	<div class="mui-btn-danger primary">GO</div>
	                        	<div class="goin ns-border-color ns-text-color">去拼團</div>
	                        </div>
	                    </div>
	                </footer>
	            </li>`;
			}
			html += '</ul>';
		}
		$('.spelling-block').html(html);
	})
}
//滑动到底部加载
$(window).scroll(function(){
	var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
	var content_box_height = parseFloat($(".spelling-block").height());
	if(totalheight - content_box_height >= 45){
		if(!is_load){
			var page = parseInt($("#page").val()) + 1;//页数
			var total_page_count = $("#page_count").val(); // 总页数
			if(page > total_page_count){
				return false;
			}else{
				getPageList(page);
			}
		}
	}
});