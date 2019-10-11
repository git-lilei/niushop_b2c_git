var lang_data = {};
$(function(){
	langApi(["hot_sale",
		"position_is",
		"goods_new",
		"competitive_products",
		"goods_no_goods_you_want"], function (res) {
		lang_data = res;
	});

	mescroll = new ScrollList("brand_scroll", getBrandList);
});

var swiper = new Swiper('.swiper-container', {
    pagination: '.swiper-pagination',
    autoplay : 3000,
});

function getBrandList(page, is_append){
	var condition = {},
    	list_html = "";
	api("System.Goods.goodsBrandList", {page_index : page, condition : condition, order : 'sort desc'}, function (res) {
    	var data = res.data;
		if (data.data.length > 0) {
			for(var i=0;i<data['data'].length;i++){
				var item = data['data'][i];
					item.brand_ads = item.brand_ads != undefined && item.brand_ads.length > 0 ? __IMG(item.brand_ads) : WAPIMG + '/goods/brand_default_adv.png';
					item.brand_pic = item.brand_pic != undefined && item.brand_pic.length > 0 ? __IMG(item.brand_pic) : WAPIMG + '/goods/brand_default_pic.png';
					item.brand_recommend = item.brand_recommend == 1 ? '<i class="recommend-icon"></i>' : '';
				list_html += `
					<div class="brand-item" onclick="location.href='`+ __URL(APPMAIN + '/goods/lists?brand_id=' + item.brand_id) +`'">
						<div class="brand-ad">
							<img src="` + item.brand_ads + `" alt="" class="pic">
							`+ item.brand_recommend +`
						</div>
						<div class="brand-info">
							<div class="brand-pic">
								<img src="` + item.brand_pic + `" >
							</div>
							<h3 class="brand-name">`+ item.brand_name +`</h3>
							<p class="brand-desc ns-text-color-gray">`+ item.describe +`</p>
						</div>
					</div>
				`;
			}
		}else{
			list_html += '<div class="empty"><img src="'+WAPIMG+'/wap_nodata.png"><p>Sorry！'+lang_data.goods_no_goods_you_want+'…</p></div>';
		}

		if (is_append) $("#brand_scroll .brand-container").append(list_html);
		else $("#brand_scroll .brand-container").html(list_html);
		mescroll.endByPage(data.total_count, data.page_count);
    })
}