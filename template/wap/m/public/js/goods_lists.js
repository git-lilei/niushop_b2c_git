-function () {
	var order = "",
		sort = "",
		min_price = "",
		max_price = "",
		mescroll;
	
	mescroll = new ScrollList("search_list_mescroll", getgoodlist);
	
	function getgoodlist(page_index, is_append) {
		
		var priceArr = [
			parseFloat($('.min-price').val()),
			parseFloat($('.max-price').val())
		];
		if (priceArr[0] != '' && priceArr[1] != '') {
			priceArr = priceArr.sort(sortNumber);
			min_price = priceArr[0];
			max_price = priceArr[1];
		}
		var keyword = $('#keyword').val();
		
		var params = {
			page_index: page_index,
			category_id: $("#category_id").val(), //种类ID
			brand_id: $("#brand_id").val(), //品牌id
			attr: $("#attr").val(),
			spec: $("#spec").val(),
			order: order,
			sort: sort,
			min_price: min_price,
			max_price: max_price,
			keyword: keyword
		};
		
		var list_html = "";
		
		api("System.Goods.goodsListByConditions", params, function (res) {
			
			var data = res.data;
			var goods_list = data.goods_list;
			if (goods_list.data.length > 0) {
				$(goods_list.data).each(function (i, v) {
					list_html += '<li class="list-item ns-border-color-gray">';
					list_html += '<div class="product-img">';
					list_html += '<a href="' + __URL(APPMAIN + '/goods/detail?goods_id=' + v['goods_id']) + '">';
					list_html += '<img src="' + __IMG(v['pic_cover_mid']) + '"/>';
					list_html += '</a>';
					list_html += '</div>';
					list_html += '<div class="list-right">';
					list_html += '<a class="pd-title pr" href="' + __URL(APPMAIN + '/goods/detail?goods_id=' + v['goods_id']) + '">';
					list_html += '<span>' + v.goods_name + '</span>';
					list_html += '</a>';
					list_html += '<div class="pd-price">';
					list_html += '<i class="ns-text-color">' + v.display_price + '</i>';
					list_html += '</div>';
					list_html += '<div class="pd-main cleart">';
					if (v.shipping_fee == 0) {
						list_html += '<span class="ns-text-color-gray">免運費</span>';
					}
					list_html += '<div class="ns-text-color-gray">';
					list_html += '<i>' + v.sales + '</i>';
					list_html += '<span>人付款</span>';
					list_html += '</div>';
					list_html += '</div>';
					list_html += '</div>';
					list_html += '</li>';
				});
			} else {
				list_html += '<div class="no-goods-list"><img src="' + WAPIMG + '/wap_nodata.png"><br>没有找到商品…</div>';
			}
			
			if (is_append) $("#search_list_mescroll ul").append(list_html);
			else $("#search_list_mescroll ul").html(list_html);
			mescroll.endByPage(goods_list.total_count, goods_list.page_count);
		});
		
	}
	
	//搜索
	$(".search").focus(function () {
		$(".search-page").css("display", "block");
		$(".classify").css("display", "none");
		$(".search-tab").css({"margin-left": "10px", "width": "80%"});
		$(".cancel").css("display", "block");
		$(".search-list").css("display", "none");
	});
	
	$(".history-text > span:nth-child(2)").click(function () {
		$(".history-list").html("");
		$.removeCookie('searchRecordWap');
		$(".search-container .cancel").click();
	});
	
	//取消
	$(".search-container .cancel").click(function () {
		$(".search-page").hide();
		$(".classify").show();
		$(".search-tab").css({"margin-left": "0", "width": "70%"});
		$(".cancel").hide();
		$(".search-list").show();
	});
	
	// 查询
	$('.search-container .icon-search').click(function () {
		var searchCont = $('.search-container .search').val();
		searchCont = searchCont.replace(/</g, "&lt;").replace(/>/g, "&gt;");
		
		if (searchCont != '') {
			if ($.cookie("searchRecordWap") != undefined) {
				var arr = eval($.cookie("searchRecordWap"));
			} else {
				var arr = new Array();
			}
			if (arr.length > 0) {
				if ($.inArray(searchCont, arr) < 0) {
					arr.push(searchCont);
				}
			} else {
				arr.push(searchCont);
			}
			$.cookie("searchRecordWap", JSON.stringify(arr));
			
			location.href = __URL(APPMAIN + '/goods/lists?keyword=' + searchCont);
		}
	});
	
	//分类
	var mask_classify_list = new MaskLayer("#head,.classify-content", function () {
		$(".classify-content").hide();
	});
	
	$(".classify").click(function () {
		if ($(".classify-content").is(":hidden")) {
			$(".classify-content").show();
			mask_classify_list.show();
		} else {
			$(".classify-content").hide();
			mask_classify_list.hide();
		}
		$("body").removeClass("mask-layer-open");
	});
	
	//清除选项
	$(".clear-search button").click(function () {
		$("div.condition-value a").removeClass("ns-border-color ns-text-color").addClass("ns-bg-color-gray-fadeout-60 ns-border-color-gray-shade-10");
		$("div.condition-value a.all").addClass("ns-border-color ns-text-color").removeClass("ns-bg-color-gray-fadeout-60 ns-border-color-gray-shade-10");
	});
	
	//筛选
	var mask_screen_list = new MaskLayer(".sift-cover", function () {
		$(".sift-cover").css("transform", "translate3d(100%,0,0)");
	});
	
	$(".sort-screen").click(function () {
		$(".sift-cover").css("transform", "translate3d(0,0,0)");
		mask_screen_list.show();
		$("body").removeClass("mask-layer-open");
		
		//关闭分类遮罩层
		$(".classify-content").hide();
		mask_classify_list.hide();
	});
	
	$(".sift-body .condition-value a").click(function () {
		$(this).addClass("ns-border-color ns-text-color").removeClass("ns-bg-color-gray-fadeout-60 ns-border-color-gray-shade-10").siblings().removeClass("ns-border-color ns-text-color").addClass("ns-bg-color-gray-fadeout-60 ns-border-color-gray-shade-10");
	});
	
	$(".click-down").click(function () {
		var is_open = $(this).attr("is_open");
		if (is_open == 0) {
			$(this).parent("li").css("height", "auto");
			$(this).attr("is_open", 1);
		} else {
			$(this).parent("li").css("height", "85px");
			$(this).attr("is_open", 0);
		}
	});
	
	// 点击取消
	$('.sift-cover .cancel').click(function (event) {
		$(".sift-cover").css("transform", "translate3d(100%,0,0)");
		mask_screen_list.hide();
	});
	
	//点击确定按钮进行筛选
	$(".sift-cover .sure").click(function () {
		var attr_array = new Array();
		var spec_array = new Array();
		$(".condition-value").each(function (i, e) {
			var screen_type = $(e).attr("data-screen-type");
			var selectedEl = $(e).find('.ns-text-color');
			//筛选品牌
			if (screen_type == "brand") {
				if (selectedEl.attr("data-brand-id") != "" && selectedEl.attr("data-brand-id") != undefined) {
					$("#brand_id").val(selectedEl.attr("data-brand-id"));
				} else {
					$("#brand_id").val("");
				}
			}
			//筛选属性
			if (screen_type == "attr") {
				if (selectedEl.attr("data-attr-value") != "" && selectedEl.attr("data-attr-value") != undefined) {
					attr_array[i] = selectedEl.attr("data-attr-value");
				} else {
					attr_array[i] = "";
				}
			}
			//筛选规格
			if (screen_type == "spec") {
				if (selectedEl.attr("data-spec-value") != "" && selectedEl.attr("data-spec-value") != undefined) {
					spec_array[i] = selectedEl.attr("data-spec-value");
				} else {
					spec_array[i] = "";
				}
			}
			//数组去空
			new_attr_array = $.grep(attr_array, function (n) {
				return $.trim(n).length > 0;
			});
			attr = new_attr_array.join(";");
			$("#attr").val(attr);
			//数组去空
			new_spec_array = $.grep(spec_array, function (n) {
				return $.trim(n).length > 0;
			});
			spec = new_spec_array.join(";");
			$("#spec").val(spec);
		});
		
		var num_rex = /^\d+.?\d{0,2}$/;
		if ($('.min-price').val() != undefined && $('.min-price').val() != ''){
			if (!num_rex.test($('.min-price').val())) {
				toast("请输入合法数字");
				return;
			}
		}
		
		if ($('.max-price').val() != undefined && $('.max-price').val() != ''){
			if (!num_rex.test($('.max-price').val())) {
				toast("请输入合法数字");
				return;
			}
		}
		
		if (parseFloat($('.max-price').val()) < parseFloat($('.min-price').val())) {
			toast("请输入正确的价格区间");
			return false;
		}
		getgoodlist(1, false);
		$(".sift-cover").css("transform", "translate3d(100%,0,0)");
		mask_screen_list.hide();
	});
	
	var judge = false;
	$(".sort-tab li").click(function () {
		$(this).parent().find("span").removeClass("ns-text-color");
		$(this).parent().find("i").removeClass("ns-text-color");
		$(this).find("span").addClass("ns-text-color");
		
		order = $(this).attr("data-order-type");
		sort = $(this).attr("data-sort");
		$(this).attr("data-sort", sort == 'asc' ? 'desc' : 'asc');
		
		if ($(this).is(".screen-price")) {
			if (judge = !judge) {
				$(this).parent().find("i").removeClass("ns-text-color");
				$(this).find(".icon-angle-up").addClass("ns-text-color");
			} else {
				$(this).parent().find("i").removeClass("ns-text-color");
				$(this).find(".icon-angle-down").addClass("ns-text-color");
			}
		} else {
			judge = false;
		}
		
		getgoodlist(1, false);
	});
	
	//样式切换
	$(".search-list").click(function () {
		if ($(".search-list i").hasClass("icon-bars")) {
			$(".search-list i").removeClass("icon-bars").addClass("icon-th-large");
			$(".list-content").addClass("largest");
		}
		else {
			$(".search-list i").removeClass("icon-th-large").addClass("icon-bars");
			$(".list-content").removeClass("largest");
		}
	});
	
	// 数字排序
	function sortNumber(a, b) {
		return a - b
	}
	
}();

function categoryGoods(obj) {
	$(".primary-classification li").removeClass("ns-text-color");
	$(obj).addClass("ns-text-color");
	var category_id = $(obj).attr('data-category-id');
	if ($(".two-stage-classification li[data-pid='" + category_id + "']").length > 0) {
		$(".two-stage-classification li").hide();
		$(".two-stage-classification li[data-pid='" + category_id + "']").show();
	} else {
		location.href = __URL(APPMAIN + "/goods/lists?category_id=" + category_id);
	}
}