$(function () {
	$(window).scroll(function (event) {
		var scrollTop = $(window).scrollTop();
		$('.line-height').height(scrollTop);
	});
	
	$('.goods-category li').click(function (event) {
		$(this).attr('class', 'active ns-bg-color').siblings('li').attr('class', '');
		loadInfo(1);
	});
	
	loadInfo(1);
});

// 加载数据
function loadInfo(page) {
	var category_id = $('.goods-category li.active').attr('data-category');
	api('System.Member.footprint', {"page_index": page, category_id: category_id}, function (res) {
		if (res.code == 0) {
			var list = res['data']['data'];
			if (page == 1) {
				$('.footprint-content section').html('');
			}
			if (list.length > 0) {
				$('.line-height,.line').removeClass('hide');
				for (var i = 0; i < list.length; i++) {
					var item = list[i];
					if (!$('#' + item.add_date).length) {
						var html = `<div class="item" id="` + item.add_date + `">
									<i class="icon icon-circle-blank ns-text-color"></i>
									<div class="tit">
										<time>` + item.add_date + `</time><a onclick="delFootprint('add_date', '` + item.add_date + `')" class="del ns-text-color-gray">删除</a>
									</div>
									<div class="cont">
										<ul class="goods-list clearfix">
											<li>
												<button type="button" class="close" onclick="delFootprint('browse_id', '` + item.browse_id + `', this)"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
												<a href="` + __URL(SHOPMAIN + '/goods/detail?goods_id=' + item.goods_id) + `" class="goods-img" title="` + item.goods_info.goods_name + `">
													<img src="` + __IMG(item.goods_info.picture_info.pic_cover_mid) + `">
												</a>
												<div class="goods-price"><i>￥` + item.goods_info.promotion_price + `</i></div>
											</li>
										</ul>
									</div>
								</div>`;
						$('.footprint-content section').append(html);
					} else {
						var html = `<li>
									<button type="button" class="close" onclick="delFootprint('browse_id', '` + item.browse_id + `', this)"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
									<a href="` + __URL(SHOPMAIN + '/goods/detail?goods_id=' + item.goods_id) + `" class="goods-img" title="` + item.goods_info.goods_name + `">
										<img src="` + __IMG(item.goods_info.picture_info.pic_cover_mid) + `">
									</a>
									<div class="goods-price"><i>￥` + item.goods_info.promotion_price + `</i></div>
								</li>`;
						$('#' + item.add_date + ' .goods-list').append(html);
					}
				}
			} else if (page == 1 && list.length == 0) {
				var html = `<div class="empty">当前分类暂无浏览记录</div>`;
				$('.line-height,.line').addClass('hide');
				$('.footprint-content section').html(html);
			}
		}
	})
}

// 删除足迹
function delFootprint(type, value, event) {
	api('System.Member.deleteFootprint', {"type": type, "value": value}, function (res) {
		if (res.code == 0) {
			show('删除成功');
			if (type == 'browse_id') $(event).parent('li').remove();
			if (type == 'add_date') $('#' + value).remove();
			location.href = __URL(SHOPMAIN + '/member/footprint');
		}
	})
}