var lang_data = {},
	mescroll,
	swiper = new Swiper('.swiper-container', {
    	pagination: '.swiper-pagination',
    	autoplay : 3000,
	});

$(function(){
	langApi(["hot_sale",
		"position_is",
		"goods_new",
		"competitive_products",
		"goods_no_goods_you_want",
		"goods_integral"], function (res) {
		lang_data = res;
	});

	mescroll = new ScrollList("point-scroll", getGoodsList);

	var maskTips = new MaskLayer(".tips-layer", function () {
		//点击遮罩层回调
		$(".tips-layer").slideUp(300);
	});
	
	$(".explain").click(function () {
		maskTips.show();//显示遮罩
		$(".tips-layer").slideDown(300);
	});
})


function getGoodsList(page, is_append){
 	var condition = {
        'ng.state' : 1,
        'ng.point_exchange_type' : ['neq', 0]
    };
    var list_html = "";
    api("System.Goods.goodsList",{page_index : page, condition : condition, order : 'sort desc,create_time desc'}, function (res) {
    	var data = res.data;
		if (data.data.length > 0) {
			for(var i=0;i<data['data'].length;i++){
				var item = data['data'][i],
					url = item.point_exchange_type == 2 ? __URL(APPMAIN + '/goods/detail?goods_id='+ item.goods_id + '&from=point') : __URL(APPMAIN + '/goods/detail?goods_id='+ item.goods_id);	
					if(item.point_exchange_type == 2 || item.point_exchange_type == 3){
						item.display_price = lang_data.goods_integral + ':<em>' + item.point_exchange +'</em>';
					}else if(item.point_exchange_type == 1){
						if(item.promotion_price > 0){
							item.display_price = '<em class="unit">￥</em>' + item.promotion_price + '+' + item.point_exchange + lang_data.goods_integral;
						}
					}

				list_html += `
					<li class="goods-item">
						<div class="imgs">
							<a href="`+ url +`">
								<img src="`+ __IMG(item.pic_cover_mid) +`">
							</a>
						</div>
						<div class="info">
							<p class="goods-title">
								<a class="ns-text-color-black" href="`+ url +`">`+ item.goods_name +`</a>
							</p>
							<div class="goods-info">
								<span class="goods_price ns-text-color">
									`+ item.display_price +`
								</span>
							</div>
						</div>
					</li>
				`;
			}
		}else{
			list_html += '<div class="empty"><img src="'+WAPIMG+'/wap_nodata.png"><p>Sorry！'+lang_data.goods_no_goods_you_want+'…</p></div>';
		}

		if (is_append) $("#goods_list_mescroll").append(list_html);
		else $("#goods_list_mescroll").html(list_html);
		mescroll.endByPage(data.total_count, data.page_count);
    })
}