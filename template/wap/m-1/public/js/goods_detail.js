$(function () {
	
	init();
	
	//-------------------------------------顶部bar-------------------------------------
	changeTopBar();
	$(window).scroll(function () {
		changeTopBar();
		if ($(this).scrollTop() > $(window).height()) $(".go-top").show();
		else $(".go-top").hide();
		
		var _self = $(this);
		$(".top-bar-flex .header-nav li").each(function () {
			var top = $(this).attr("data-top");
			if (_self.scrollTop() >= top) {
				$(this).addClass("ns-text-color ns-border-color").removeClass("ns-text-color-gray").siblings().removeClass("ns-text-color ns-border-color").addClass("ns-text-color-gray");
			}
		});
		
	});
	
	//返回顶部
	$(".go-top").click(function () {
		$('body,html').animate({
			scrollTop: 0
		}, 300);
	});
	
	$(".top-bar-flex .header-nav li").click(function () {
		$(this).addClass("ns-text-color ns-border-color").removeClass("ns-text-color-gray").siblings().removeClass("ns-text-color ns-border-color").addClass("ns-text-color-gray");
		var top = 0;
		var flag = $(this).attr("data-flag");
		switch (flag) {
			case "goods":
				top = 0;
				break;
			case "evaluation" :
				top = $(".product-evaluation-main").offset().top - ($(".product-evaluation-main").height() / 2);
				break;
			case "details":
				top = $(".product-details").offset().top;
				break
		}
		
		$('body,html').animate({
			scrollTop: top
		}, 300);
	});
	
	countDown();
	
	function changeTopBar() {
		if ($(window).scrollTop() > 150) $(".top-bar-flex").addClass("transparent");
		else $(".top-bar-flex").removeClass("transparent");
	}
	
	//-------------------------------------顶部bar-------------------------------------
	
	//-------------------------------------商品轮播图-------------------------------------
	var swiper = new Swiper('.product-media', {
		pagination: '.swiper-pagination',
		loop: true,
		autoplay: 3000
	});
	
	$(".goods-alter span").click(function () {
		$(this).addClass('ns-bg-color').siblings().removeClass('ns-bg-color');
		if ($(this).index() == 1) {
			$(".video-wrap .video-js").css("height", $(".product-media").height() + "px");
			$(".video-wrap").show();
			$(".product-media").hide();
			swiper.stopAutoplay()
		} else {
			$(".video-wrap").hide();
			$(".product-media").show();
			swiper.startAutoplay()
		}
	});
	//-------------------------------------商品轮播图-------------------------------------
	
	//-------------------------------------优惠券-------------------------------------
	if ($(".product-coupon-popup-layer").length > 0) {
		var maskCouponProduct = new MaskLayer(".product-coupon-popup-layer", function () {
			//点击遮罩层回调
			$(".product-coupon-popup-layer").slideUp(300);
		});
		
		$(".product-coupon").click(function () {
			maskCouponProduct.show();
			$(".product-coupon-popup-layer").slideDown(300);
		});
		
		$(".product-coupon-popup-layer .confirm").click(function () {
			maskCouponProduct.hide();
			$(".product-coupon-popup-layer").slideUp(300);
		});
		
		//领取优惠劵
		var is_click = false;
		$(".product-coupon-popup-layer .item").on('click', function () {
			if (!$(this).hasClass("receive")) {
				
				if (uid == "") {
					location.href = __URL(APPMAIN + "/login");
					return;
				}
				var coupon_type_id = $(this).attr("data-coupon-id");
				var $this = $(this);
				if (is_click) return false;
				is_click = true;
				
				api("System.Goods.receiveGoodsCoupon", {
					"coupon_type_id": coupon_type_id,
					"scenario_type": 3
				}, function (res) {
					$(".product-coupon-popup-layer .confirm").click();
					is_click = false;
					var data = res.data;
					if (data > 0) {
						toast("領取成功");
					} else if (data == -2010) {
						toast("您已領取最大上限！");
					} else if (data == -2011) {
						$($this).addClass("received");
						toast("來遲了，已經領完了");
					} else if (data == -2019) {
						toast("您已領取最大上限！");
					} else {
						show(res['message']);
					}
				});
			} else {
				toast("已領取");
			}
		});
	}
	//-------------------------------------优惠券-------------------------------------
	
	//-------------------------------------阶梯优惠-------------------------------------
	var maskProductLadderPreferential = new MaskLayer(".product-ladder-preferential-popup-layer", function () {
		//点击遮罩层回调
		$(".product-ladder-preferential-popup-layer").slideUp(300);
	});
	
	$(".product-ladder-preferential").click(function () {
		maskProductLadderPreferential.show();//显示遮罩
		$(".product-ladder-preferential-popup-layer").slideDown(300);
	});
	
	$(".product-ladder-preferential-popup-layer .js-confirm").click(function () {
		maskProductLadderPreferential.hide();
		$(".product-ladder-preferential-popup-layer").slideUp(300);
	});
	//-------------------------------------阶梯优惠-------------------------------------
	
	
	//-------------------------------------服务-------------------------------------
	var maskProductService = new MaskLayer(".product-merchants-service-popup-layer", function () {
		//点击遮罩层回调
		$(".product-merchants-service-popup-layer").slideUp(300);
	});
	
	$(".product-merchants-service").click(function () {
		maskProductService.show();//显示遮罩
		$(".product-merchants-service-popup-layer").slideDown(300);
	});
	
	$(".product-merchants-service-popup-layer .js-confirm").click(function () {
		maskProductService.hide();
		$(".product-merchants-service-popup-layer").slideUp(300);
	});
	//-------------------------------------服务-------------------------------------
	
	//-------------------------------------属性-------------------------------------
	if ($(".product-attribute-popup-layer").length > 0) {
		var maskProductAttribute = new MaskLayer(".product-attribute-popup-layer", function () {
			//点击遮罩层回调
			$(".product-attribute-popup-layer").slideUp(300);
		});
		
		$(".product-attribute").click(function () {
			maskProductAttribute.show();//显示遮罩
			$(".product-attribute-popup-layer").slideDown(300);
		});
		
		$(".product-attribute-popup-layer .js-confirm").click(function () {
			maskProductAttribute.hide();
			$(".product-attribute-popup-layer").slideUp(300);
		});
	}
	//-------------------------------------属性-------------------------------------
	
	//-------------------------------------评价-------------------------------------
	$(".product-evaluation-main .review-content ul.filter li").click(function () {
		$(this).addClass("ns-bg-color").removeClass("ns-bg-color-gray-fadeout-60").siblings().removeClass("ns-bg-color").addClass("ns-bg-color-gray-fadeout-60");
		evaluation_list_mescroll.triggerDownScroll();
	});
	
	$(".product-evaluation-main .view-more").click(function () {
		$(".product-evaluation-main .mui-cover").addClass("show");
	});
	
	$(".product-evaluation-main .mui-cover .back").click(function () {
		$(".product-evaluation-main .mui-cover").removeClass("show");
	});
	
	api("System.Goods.goodsEvaluateCount", {goods_id: goods_id}, function (res) {
		var data = res.data;
		if (data) {
			$(".js-evaluate-count").text(data.evaluate_count);
			$(".js-evaluate-imgs-count").text(data.imgs_count);
			$(".js-evaluate-praise-count").text(data.praise_count);
			$(".js-evaluate-center-count").text(data.center_count);
			$(".js-evaluate-bad-count").text(data.bad_count);
		}
	});
	
	//-------------------------------------分享-------------------------------------
	var maskShare = new MaskLayer(".share-popup", function () {
		$('.poster-popup').css('bottom', '-200%');
		$('.share-popup,.wechat-share').hide();
	});
	
	$('.product-share').click(function (event) {
		maskShare.show();
		$('.share-popup').show();
	});
	
	var posterWhith = $('.poster-popup .poster-wrap').width(),
		ratio = parseFloat((740 / posterWhith).toFixed(2));
	if (uid != null && uid != "") {
		var posterHeight = parseInt(1240 / ratio);
	} else {
		var posterHeight = parseInt(1100 / ratio);
	}
	$('.poster-popup .poster-wrap').height(posterHeight);
	$('.poster-popup').height((posterHeight + 120));
	
	// 生成海报
	$('.share-popup .poster').click(function (event) {
		$('.poster-popup').css('bottom', 0);
		$('.wechat-share').hide();
		$.ajax({
			url: __URL(APPMAIN + '/goods/createGoodsPoster'),
			type: 'post',
			data: {goods_id: goods_id, uid: uid},
			success: function (res) {
				if (res.code > 0) {
					$('.poster-popup .poster-wrap').html('<img src="' + __IMG(res.path) + '?rand=' + Math.random() + '">');
					$('.poster-popup .save-btn').attr({'download': '', 'href': __IMG(res.path)});
				} else {
					$('.poster-popup .poster-wrap').html('<p class="error ns-text-color-gray">海報生成失敗</p>');
				}
			}
		})
	});
	
	$('.share-popup .wechat').click(function (event) {
		$('.wechat-share').show();
	});
	
	//-------------------------------------分享-------------------------------------
	
	var page_size = 15;
	var evaluation_list_mescroll = new ScrollList("evaluation_list_mescroll", loadProductEvaluateList, page_size);
	
	function loadProductEvaluateList(page_index, is_append) {
		var comments_type = $(".product-evaluation-main .review-content ul.filter li.ns-bg-color").attr('data-type');
		api("System.Goods.goodsComments", {
			'comments_type': comments_type,
			"goods_id": goods_id,
			"page_index": page_index
		}, function (res) {
			
			var data = res.data;
			if (res.code == 0) {
				
				var list = data.data;
				var h = "";
				if (list.length > 0) {
					
					for (i in list) {
						var item = list[i];
						
						var member_name = item['member_name'];
						member_name = item['is_anonymous'] == 1 ? member_name.replace(member_name.substring(1, member_name.length), '***') + lang_goods_detail.anonymous : member_name;
						
						var user_img = DEFAULT_HEAD_IMG;
						if (item['user_img'] != undefined && item["user_img"] != "" && item['user_img'] != 0) {
							user_img = __IMG(item['user_img']);
						}
						
						if (i == 0) {
							$(".js-first-evaluate").show();
							$(".js-first-evaluate .user img").attr("src", user_img);
							$(".js-first-evaluate .user span").text(member_name);
							$(".js-first-evaluate .product-content").text(item['content']);
							$(".js-first-evaluate .date").text(timeStampTurnTime(item['addtime']));
						}
						
						h += '<li class="item ns-border-color-gray">';
						h += '<div class="info ns-text-color-gray">';
						h += '<div class="author">';
						h += '<img src="' + user_img + '">';
						h += '<span class="nick">' + member_name + '</span>';
						h += '</div>';
						h += '<time>' + timeStampTurnTime(item['addtime']) + '</time>';
						h += '</div>';
						
						h += '<blockquote>' + item['content'] + '</blockquote>';
						if (item.image != "") {
							var evaluate_img_arr = item.image.split(",");
							h += '<ul class="pics">';
							for (var ei = 0; ei < evaluate_img_arr.length; ei++) {
								h += '<li><img class="comment-pic" src="' + __IMG(evaluate_img_arr[ei]) + '" alt="用户评论"  onclick="showImgSlider(this);" data-index="' + ei + '" data-preview-src="' + __IMG(evaluate_img_arr[ei], "MID") + '" data-preview-group="' + i + '"></li>';
							}
							h += '</ul>';
						}
						
						//店家回复
						// item['explain_first'] = "测试用";
						if (item['explain_first'] != '') {
							h += ' <div class="evaluation-reply ns-bg-color-gray-fadeout-60">' + lang_goods_detail.goods_shopkeeper_replies + '：' + item['explain_first'] + '</div>';
						}
						
						//追评
						// item['again_content'] = "测试用";
						if (item['again_content'] != '') {
							h += '<p class="review-evaluation">追加評價<time class="review-time ns-text-color-gray">' + timeStampTurnTime(item['again_addtime']) + '</time></p>';
							h += '<div class="evaluation-content review">' + item['again_content'] + '</div>';
							//item['again_explain'] = "测试用";
							if (item['again_image'] != '') {
								var imgs_arr = item['again_image'].split(',');
								h += '<ul class="evaluation-pics">';
								for (var key in imgs_arr) {
									h += '<li><img src="' + __IMG(imgs_arr[key]) + '" onclick="showImgSlider(this);" data-index="' + key + '" class="comment-pic"></li>';
								}
								h += '</ul>';
							}
							if (item['again_explain'] != '') {
								h += '<div class="evaluation-reply">' + lang_goods_detail.goods_shopkeeper_replies + '：' + item['again_explain'] + '</div>';
							}
						}
						
						// if (item.product_sku_name != "") {
						// 	h += '<ul class="sku ns-text-color-gray">';
						// 	h += '<li>' + 1111 + '</li>';
						// 	h += '</ul>';
						// }
						h += '</li>';
					}
				} else {
					h = '<li class="item error ns-text-color-gray">該商品暫無評價。</li>';
				}
				if (is_append) $("#evaluation_list_mescroll>ul").append(h);
				else $("#evaluation_list_mescroll>ul").html(h);
				evaluation_list_mescroll.endByPage(data.total_count, data.page_count);
			} else {
				evaluation_list_mescroll.endErr();
				$(".product-evaluation-main").html('<p class="empty ns-text-color-gray">該商品暫無評價</p>');
			}
		});
	}
	
	//-------------------------------------评价-------------------------------------
	
	//-------------------------------------底部操作-------------------------------------
	var maskProductBottomBar = new MaskLayer(".product-bottom-bar .widgets-cover", function () {
		if ($("#hidden_is_virtual").val() == 1) {
			$(".product-bottom-bar .bargain-receiver-mobile").hide();
			$(".product-bottom-bar .widgets-cover").css("top", "30%");
		} else {
			$(".product-bottom-bar .bargain-address").hide();
		}
		$(".product-bottom-bar .widgets-cover").removeClass("show");
		$(".product-bottom-bar .sku-wrap").show();
	});
	
	$(".product-bottom-bar .right-operation>a").click(function () {
		fbTouch.TouchLead();
		maskProductBottomBar.show();
		$(".product-bottom-bar .js-submit").attr("data-tag", $(this).attr("data-tag"));
		top_permissions = $(this).attr("data-top-permissions");//最高权限，用于是否允许改变价格
		
		//部分活动商品需要动态改变订单类型，例如拼团商品要区分单独购买和参与拼团
		var order_type = $(this).attr("data-order-type");
		if (order_type) $("#hidden_order_type").val(order_type);
		
		changeProductSku();
		$(".product-bottom-bar .widgets-cover").addClass("show");
	});
	
	$(".product-bottom-bar .sku-close").click(function () {
		maskProductBottomBar.hide();
		$(".product-bottom-bar .widgets-cover").removeClass("show");
	});
	
	$(".product-bottom-bar .js-submit").click(function () {
		fbTouch.TouchAddToCart();
		var pay_way = $(this).attr("data-tag");
		if (uid != "") {
			
			if ($(this).hasClass("disabled")) return;
			
			if (sku_id == null || sku_id == "") return;
			
			if ($("#hidden_stock").val() == 0) {
				toast("商品已售罄");
				return;
			}
			
			if (pay_way == "add_cart") {
				
				var cart_detail = {
					goods_id: goods_id,
					count: $("#buy_number").val(),
					goods_name: goods_name,
					sku_id: sku_id,
					sku_name: sku_name,
					price: price,
					picture_id: $("#hidden_picture_id").val(),
					shop_name: $("#hidden_shop_name").val()
				};
				
				api('System.Goods.addCart', {"cart_detail": JSON.stringify(cart_detail)}, function (res) {
					var data = res.data;
					maskProductBottomBar.hide();
					$(".product-bottom-bar .widgets-cover").removeClass("show");
					if (data.code > 0) {
						toast("加入購物車成功");
					} else if (data.code == -1) {
						toast("只有會員登錄之後才能購買，請進入會員中心註冊或登錄。", __URL(APPMAIN + "/member"));
					} else if (data.code == 0) {
						toast(data.message);
					}
				});
				
			} else if (pay_way == "buy_now") {
				
				//检测商品限购，是否允许购买
				getGoodsPurchaseRestrictionForCurrentUser(goods_id, $("#buy_number").val(), function (purchase) {
					if (purchase.code > 0) {
						var order_type = $("#hidden_order_type").val() ? $("#hidden_order_type").val() : 1;// 1 普通订单	4 拼团订单	6 预售订单	7 砍价订单
						var promotion_type = $("#hidden_promotion_type").val();//1 组合套餐	2 团购	3 砍价	4 积分兑换
						var data = JSON.stringify({
							order_type: order_type,
							goods_sku_list: sku_id + ":" + $("#buy_number").val(),
							promotion_type: promotion_type,
							promotion_info: {
								tuangou_group_id: $("#hidden_tuangou_group_id").val()
							}
						});
						
						$.ajax({
							type: 'post',
							url: __URL(APPMAIN + "/order/addOrderCreateData"),
							dataType: "JSON",
							data: {data: data},
							success: function (res) {
								location.href = __URL(APPMAIN + "/order/payment");
							}
						});
					} else {
						toast(purchase.message);
						location.href = __URL(APPMAIN + "/login/index");
					}
					
				});
			} else if (pay_way == "bargain") {
				
				$(".product-bottom-bar .sku-wrap").hide();
				if ($("#hidden_is_virtual").val() == 1) {
					$(".product-bottom-bar .bargain-receiver-mobile").show();
					$(".product-bottom-bar .widgets-cover").css("top", "75%");
				} else {
					$(".product-bottom-bar .bargain-address").show();
				}
				
			}
			
		} else {
			window.location.href = __URL(APPMAIN + "/login");
		}
	});
	
	//砍价所需要的收货地址和自提地址
	$(".bargain-address nav ul li").click(function () {
		$(this).addClass("selected ns-border-color-hover ns-text-color-hover").removeClass("ns-border-color-gray-fadeout-50").siblings().removeClass("selected ns-border-color-hover ns-text-color-hover").addClass("ns-border-color-gray-fadeout-50");
		var type = $(this).data("type");
		$(".bargain-address>ul[data-type]").hide();
		$(".bargain-address>ul[data-type='" + type + "']").show();
	});
	
	//选择砍价地址
	$(".product-bottom-bar .bargain-address>ul li[data-id]").click(function () {
		var type = $(this).parent().data("type");
		var id = $(this).data("id");
		
		getGoodsPurchaseRestrictionForCurrentUser(goods_id, $("#buy_number").val(), function (purchase) {
			if (purchase.code > 0) {
				api('NsBargain.Bargain.addBargain', {
					"sku_id": sku_id,
					"num": $("#buy_number").val(),
					"address_id": id,
					"bargain_id": $("#hidden_bargain_id").val(),
					"distribution_type": type
				}, function (res) {
					window.location.href = __URL(APPMAIN + "/goods/bargainlaunch?launch_id=" + res.data.launch_id);
				});
			} else {
				toast(purchase.message);
			}
		});
		
	});
	
	$(".product-bottom-bar .bargain-receiver-mobile .footer").click(function () {
		var receiver_mobile = $("#receiver_mobile").val();
		if (receiver_mobile.search(regex.mobile) == -1) {
			toast('請輸入正確的行動號碼');
			$("#receiver_mobile").focus();
			return false;
		}
		getGoodsPurchaseRestrictionForCurrentUser(goods_id, $("#buy_number").val(), function (purchase) {
			if (purchase.code > 0) {
				api('NsBargain.Bargain.addBargain', {
					"sku_id": sku_id,
					"num": $("#buy_number").val(),
					"receiver_mobile": receiver_mobile,
					"bargain_id": $("#hidden_bargain_id").val(),
					"distribution_type": "virtual"
				}, function (res) {
					window.location.href = __URL(APPMAIN + "/goods/bargainlaunch?launch_id=" + res.data.launch_id);
				});
			} else {
				toast(purchase.message);
			}
		});
	});
	
	function changeProductSku() {
		
		//匹配当前选中的商品规格，找到sku_id
		var sku_length = $(".widgets-cover .sku-list-wrap").length;//应选中规格数量
		var current_sku_length = $('.widgets-cover .sku-list-wrap a.selected').length;//实际选中规格数量
		var current_goods_sku_name = [];
		
		if ($("input[name='product_sku']").length > 1) {
			$("input[name='product_sku']").each(function () {
				
				var value = $(this).val();
				var match_sku_count = 0;//匹配规格数量
				current_goods_sku_name = [];//每次匹配时都要清空
				
				$('.widgets-cover .sku-list-wrap a.selected:not(.disabled)').each(function () {
					if (value.indexOf($(this).data("id")) > -1) {
						match_sku_count++;
						current_goods_sku_name.push($(this).parent().prev().text() + ":" + $(this).text());
					}
				});
				
				if (sku_length == match_sku_count && current_sku_length == match_sku_count) {
					sku_id = $(this).attr("data-sku-id");
					if (parseInt($(this).attr("data-picture")) > 0) {
						$("#hidden_picture_id").val($(this).attr("data-picture"));
						$(".js-thumbnail").attr("src", $(this).attr("data-default-img"));
					} else {
						$(".js-thumbnail").attr("src", $("#hidden_default_img").val());
						$("#hidden_picture_id").val($("#hidden_default_picture_id").val());
					}
					
					return false;
				}
			});
		} else {
			sku_id = $("input[name='product_sku']").attr("data-sku-id");
		}
		
		var current_sku = $("input[name='product_sku'][data-sku-id=" + sku_id + "]");
		var price = current_sku.data("price");
		var stock = current_sku.data("stock");
		
		if (current_goods_sku_name.length > 0) {
			var html = '已選擇 ';
			for (i in current_goods_sku_name) {
				html += '<span>' + current_goods_sku_name[i] + '</span>';
			}
			$(".product-bottom-bar .sku-wrap .header .main .sku-info").html(html);
		} else {
			$(".product-bottom-bar .sku-wrap .header .main .sku-info").html("");
		}
		
		$(".product-bottom-bar .sku-wrap .header .main .stock").text(lang_goods_detail.goods_stock + stock + lang_goods_detail.goods_piece);
		
		if (stock == 0) {
			$(".js-submit").addClass("disabled");
		} else {
			$(".js-submit").removeClass("disabled");
		}
		
		// 预售、团购、拼团、积分等活动不需要改变价格
		if ((top_permissions == 1) || ($("#hidden_point_exchange_type").val() != 2 && $("#hidden_point_exchange_type").val() != 3 && $("#hidden_promotion_type").val() != 2 && $("#hidden_order_type").val() != 4 && $("#hidden_order_type").val() != 6 && $("#hidden_promotion_type").val() != 7)) {
			
			if ($("#hidden_promotion_type").val() == 3) {
				//砍价活动取原价
				price = current_sku.data("original-price");
			}
			
			var point_text = "";
			if ($("#hidden_point_exchange_type").val() == 1 && $("#hidden_point_exchange").val() > 0) {
				point_text = "+" + $("#hidden_point_exchange").val() + lang_goods_detail.goods_integral;
			}
			price = calculated_price(goods_id);
			$(".product-bottom-bar .sku-wrap .header .main .price").text("NT$" + price + point_text);
		}
	}
	//计算阶梯优惠
	function calculated_price(goodsid) {
		var numObj = $("#buy_number"),
			num = parseInt(numObj.val());
		var current_sku = $("input[name='product_sku'][data-sku-id=" + sku_id + "]");
		var price = current_sku.data("price");
		var stock = current_sku.data("stock");
		var goods_ladder_preferential = $("#goods_ladder_preferential").val();
		if (goods_ladder_preferential == '') return price;
		var arr = JSON.parse(goods_ladder_preferential);

		if (arr.length > 0) {
			for (var i = 0; i < arr.length; i++) {
				var item = arr[i];
				if (num >= item['quantity'] && item['goods_id'] == goodsid) {
					price -= item['price'];
					break;
				}
			}
		}
		return price;
	}
	// 点击选择规格
	$('.widgets-cover .sku-list-wrap a').click(function () {
		$(this).addClass('selected').siblings().removeClass('selected');
		changeProductSku();
	});

	// 购买数量加减
	$('.widgets-cover .number-wrap button').click(function () {
		
		var numObj = $("#buy_number"),
			num = parseInt(numObj.val()),
			max_buy = numObj.data('max-buy'),
			min_buy = numObj.data('min-buy'),
			_this = $(this);
		
		if (_this.attr('data-operator') == '+') {
			// 加
			if (num < max_buy) {
				num += 1;
			} else {
				return;
			}
		} else if (_this.attr('data-operator') == '-') {
			// 减
			if ((num > min_buy && min_buy > 0) || (num > 1 && min_buy == 0)) {
				num -= 1;
			} else {
				return;
			}
		}
		numObj.val(num);
		
		changeProductSku();
	});
	
	$("#buy_number").keyup(function () {
		var max_buy = parseInt($(this).data("max-buy").toString());
		var min_buy = parseInt($(this).data("min-buy").toString());
		var v = $(this).val();
		
		if (min_buy > 0 && v < min_buy) {
			$(this).val(min_buy);
			return;
		}
		
		if (max_buy > 0 && v > max_buy) {
			$(this).val(max_buy);
		}
	});
	
	changeProductSku();

//-------------------------------------底部操作-------------------------------------
	/*
	 * 收藏商品
	 */
	var is_click_collection = false;
	$(".js-collection").click(function () {
		
		if (uid != "") {
			var _this = $(this);
			var whether_collection = _this.data("whether-collection");
			if (is_click_collection) return;
			is_click_collection = true;
			
			//未收藏添加收藏
			if (whether_collection == 0) {
				api('System.Member.addCollection', {
					"fav_id": goods_id,
					"fav_type": "goods",
					"log_msg": goods_name
				}, function (res) {
					var data = res.data;
					if (data > 0) {
						_this.data("whether-collection", 1).find("i").attr("class", "fa fa-heart");
						toast("收藏成功");
					}
					is_click_collection = false;
				});
			} else {
				//已收藏取消收藏
				api('System.Member.cancelCollection', {"fav_id": goods_id, "fav_type": "goods"}, function (res) {
					var data = res.data;
					if (data > 0) {
						_this.data("whether-collection", 0).find("i").attr("class", "fa fa-heart-o");
						toast("取消收藏成功");
					}
					is_click_collection = false;
				});
			}
		} else {
			location.href = __URL(APPMAIN + "/login");
		}
	});

// 更新商品点击量
	api("System.Goods.modifyGoodsClicks", {goods_id: goods_id}, function (res) {
	});

// 添加足迹
	api("System.Goods.addGoodsBrowse", {goods_id: goods_id}, function (res) {
	});
	
	niushop.share({flag: "goods", "goods_id": goods_id});

//-------------------------------------拼团-------------------------------------
	countDownSpellingEndTime();
	var notice_index = 0;
	var notice_autoTimer = 0;//全局变量目的实现左右点击同步
	
	//自动轮播
	if ($(".spelling-block ul li").length > 1) {
		$(".spelling-block ul li:eq(0)").clone(true).appendTo($(".spelling-block ul"));//克隆第一个放到最后(实现无缝滚动)
		var liHeight = $(".spelling-block").height();//一个li的高度
		//获取li的总高度再减去一个li的高度(再减二个Li是因为克隆了多出了一个Li的高度)
		var totalHeight = ($(".spelling-block ul li").length * $(".spelling-block ul li").eq(0).height()) - liHeight;
		$(".spelling-block ul").height(totalHeight);//给ul赋值高度
		notice_autoTimer = setInterval(function () {
			notice_index++;
			if (notice_index > $(".spelling-block ul li").length - 1) {
				notice_index = 0;
			}
			$(".spelling-block ul").stop().animate({
				top: -notice_index * liHeight
			}, 500, function () {
				if (notice_index == $(".spelling-block ul li").length - 1) {
					$(".spelling-block ul").css({top: 0});
					notice_index = 0;
				}
			});
		}, 5000);
	}
	
	var mask_layer_spelling_timer = null;
	var mask_layer_spelling_time = "";
	//去拼单
	$(".spelling-block ul li button").click(function () {
		
		$(".mask-layer-bg").show();
		$(".mask-layer-spelling").css({
			marginTop: -($(".mask-layer-spelling").outerHeight() / 2),
			marginLeft: -($(".mask-layer-spelling").outerWidth() / 2)
		}).show();
		$(".mask-layer-spelling>p>strong").text($(this).attr("data-poor-num"));
		if (mask_layer_spelling_timer != null) {
			clearInterval(mask_layer_spelling_timer);
		}
		$(".mask-layer-spelling .user-list .boss").next().attr("src", $(this).parent().find(".user-logo>img").attr("src"));
		mask_layer_spelling_time = $(this).attr("data-end-time");
		if (null != mask_layer_spelling_time && "" != mask_layer_spelling_time) {
			var sys_second = (mask_layer_spelling_time - ($("#current_time").val() / 1000));///1000;
			if (sys_second > 1) {
				sys_second -= 1;
				var day = Math.floor((sys_second / 3600) / 24);
				var hour = Math.floor((sys_second / 3600) % 24);
				var minute = Math.floor((sys_second / 60) % 60);
				var second = Math.floor(sys_second % 60);
				var s_hour = hour < 10 ? "0" + hour : hour;
				var s_minute = minute < 10 ? "0" + minute : minute;
				var s_second = second < 10 ? "0" + second : second;
				var str = s_hour + ":" + s_minute + ":" + s_second;
				$(".mask-layer-spelling>p>time").text(str + "後結束");
				$("#hidden_tuangou_group_id").val($(this).attr("data-group-id"));
				$(".mask-layer-spelling button").removeClass("disabled").removeAttr("disabled");
			} else {
				$(".mask-layer-spelling>p>time").text("拼單已結束");
				$("#hidden_tuangou_group_id").val(0);
				$(".mask-layer-spelling button").addClass("disabled").attr("disabled", "disabled");
			}
			mask_layer_spelling_timer = setInterval(function () {
				if (sys_second > 1) {
					sys_second -= 1;
					var day = Math.floor((sys_second / 3600) / 24);
					var hour = Math.floor((sys_second / 3600) % 24);
					var minute = Math.floor((sys_second / 60) % 60);
					var second = Math.floor(sys_second % 60);
					var s_hour = hour < 10 ? "0" + hour : hour;
					var s_minute = minute < 10 ? "0" + minute : minute;
					var s_second = second < 10 ? "0" + second : second;
					var str = s_hour + ":" + s_minute + ":" + s_second;
					$(".mask-layer-spelling>p>time").text(str + "後結束");
				} else {
					$(".mask-layer-spelling>p>time").text("拼單已結束");
					clearInterval(mask_layer_spelling_timer);
				}
			}, 1000);
		}
	});
	
	//关闭
	$(".mask-layer-spelling-close").click(function () {
		$(".mask-layer-bg").hide();
		$(".mask-layer-spelling").hide();
		$("#hidden_tuangou_group_id").val(0);
	});
	
	//参与拼团
	$(".mask-layer-spelling button").click(function () {
		if (!$(this).hasClass("disabled")) {
			$(".mask-layer-bg").hide();
			$(".mask-layer-spelling").hide();
			$(".product-bottom-bar .right-operation>a:last-child").click();
		}
	});
	
});

function getGoodsPurchaseRestrictionForCurrentUser(goods_id, num, callBack) {
	api('System.Goods.goodsPurchaseRestriction', {"goods_id": goods_id, "num": num}, function (res) {
		if (res.code == 0) {
			if (callBack) callBack(res.data);
		} else {
			toast(res.message);
		}
	}, false);
}

function countDown() {
	var end_time = $(".product-discount .countdown .txt").attr('data-value'),
		_date = ($("#current_time").val() / 1000),
		surplus_time = end_time - _date,
		timer = setInterval(function () {
			if (surplus_time > 0) {
				surplus_time -= 1;
				var day = Math.floor((surplus_time / 3600) / 24);
				var hour = Math.floor((surplus_time / 3600) % 24);
				var min = Math.floor((surplus_time / 60) % 60);
				var second = Math.floor(surplus_time % 60);
				if (day > 0) $('#day').text(day < 10 ? "0" + day : day);
				else $('#day').text("00");
				
				$('#hour').text(hour < 10 ? "0" + hour : hour);
				$('#min').text(min < 10 ? "0" + min : min);
				$('#second').text(second < 10 ? "0" + second : second);
			} else {
				clearInterval(timer);
				// location.reload();
			}
		}, 1000);
}

//点赞
var flag = false;

function clickPoint() {
	if (uid != null && uid != "") {
		if (flag) return;
		flag = true;
		api('System.Goods.giveGifts', {"goods_id": goods_id}, function (res) {
			var data = res.data;
			if (data > 0) {
				toast("點贊成功");
			} else {
				toast("點贊失敗");
			}
			flag = false;
		})
	} else {
		location.href = __URL(APPMAIN + "/login");
	}
}

// {if condition="$goods_detail.is_virtual == 0"}

//定位查询运费
// api('System.Goods.shippingFeeByLocation', {"goods_id" : goods_id }, function(res) {
// 	var data = res.data;
// 	if(data != ""){
// 		if(typeof data == "string"){
// 			$(".js-shipping-fee-name").text("运费：" + data);
// 		}else if(typeof data == "object"){
// 			$(".js-shipping-fee-name").text("运费：" + data[0].express_fee);
// 		}
// 	}
// })
// {/if}

var openPhotoSwipe = function (index) {
	var pswpElement = document.querySelectorAll('.pswp')[0];
	var items = new Array();
	$("ul.pic_list li div img.pp_init_img").each(function (i, e) {
		var theImage = new Image();
		theImage.src = $(e).attr("src");
		var info = {"src": $(e).attr("src"), "w": theImage.width, "h": theImage.height};
		items.push(info);
	})
	var options = {
		history: false,
		focus: false,
		showAnimationDuration: 0,
		hideAnimationDuration: 0,
		index: index
	};
	var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
	gallery.init();
};

$("ul.pic_list li div img.pp_init_img").bind("click", function () {
	var index = $(this).data("index");
	openPhotoSwipe(index);
});

var img_slider = null;

//点击显示评论大图
function showImgSlider(event) {
	var parent = $(event).parent().parent();
	var html = '';
	var curr = parseInt($(event).attr("data-index"));
	parent.children("li").each(function (i) {
		html += '<li style="width: ' + $(window).outerWidth() + 'px; height: ' + $(window).outerHeight() + 'px; display: table-cell; padding: 0; margin: 0; float: left;">';
		html += '<a href="javascript:;" style="display: -webkit-box;-webkit-box-align: center;-webkit-box-pack: center;">';
		html += '<img src="' + $(this).children("img").attr("src") + '">';
		html += '</a>';
		html += '</li>';
	});
	$("#img-slider ul").html(html);
	var num = parent.children("li").length;
	$('#img-slider .img-count .sum').text(num);
	$('#img-slider .img-count .curr').text(curr + 1);
	if (img_slider == null) {
		img_slider = new TouchSlider({
			id: 'img-slider',
			'auto': '-1',
			fx: 'ease-out',
			direction: 'left',
			speed: 600,
			timeout: 5000,
			'before': function (index) {
				$('#img-slider .img-count .curr').text($("#img-slider li:eq(" + index + ")").index() + 1);
			}
		});
	} else {
		img_slider.length = num;//对象已存在，修改图片数量即可
		$("#img-slider ul").css("width", ($(window).outerWidth() * num) + "px");
	}
	$("#img-slider").show().removeAttr("data-flag");
	img_slider.specified(curr);
}

//关闭遮罩层，并给予标识
$("#img-slider").click(function () {
	$(this).hide().attr("data-flag", 1);
});

// window.onload = function () {
// 	if (typeof window.WeixinJSBridge != 'undefined') {
// 		document.addEventListener("WeixinJSBridgeReady", onWeixinReady, false);
// 	} else {
// 		$("#p-detailoff").show();
// 	}
// };
//
// function onWeixinReady() {
// 	WeixinJSBridge.invoke('getNetworkType', {}, function (e) {
// 		WeixinJSBridge.log(e.err_msg);
// 		var state = e.err_msg.split(':')[1];
// 		if (state == "wifi") {
// 			$("#content").html(hdata);
// 			$("#p-detail").show();
// 		} else {
// 			$("#p-detailoff").show();
// 		}
// 	});
// }

function commonCountDown(time, obj) {
	if (null != time && "" != time) {
		var sys_second = (time - ($("#current_time").val() / 1000));///1000;
		if (sys_second > 1) {
			sys_second -= 1;
			var day = Math.floor((sys_second / 3600) / 24);
			var hour = Math.floor((sys_second / 3600) % 24);
			var minute = Math.floor((sys_second / 60) % 60);
			var second = Math.floor(sys_second % 60);
			var s_hour = hour < 10 ? "0" + hour : hour;
			var s_minute = minute < 10 ? "0" + minute : minute;
			var s_second = second < 10 ? "0" + second : second;
			var str = s_hour + ":" + s_minute + ":" + s_second;
			obj.text("剩余：" + str);
		} else {
			obj.text("拼單已結束");
		}
		var timer = setInterval(function () {
			if (sys_second > 1) {
				sys_second -= 1;
				var day = Math.floor((sys_second / 3600) / 24);
				var hour = Math.floor((sys_second / 3600) % 24);
				var minute = Math.floor((sys_second / 60) % 60);
				var second = Math.floor(sys_second % 60);
				var s_hour = hour < 10 ? "0" + hour : hour;
				var s_minute = minute < 10 ? "0" + minute : minute;
				var s_second = second < 10 ? "0" + second : second;
				var str = s_hour + ":" + s_minute + ":" + s_second;
				obj.text("剩余：" + str);
			} else {
				obj.text("拼單已結束");
				clearInterval(timer);
			}
		}, 1000);
	}
}

//拼单倒计时
function countDownSpellingEndTime() {
	$("[id^='spelling_end_time']").each(function () {
		var self = $(this);
		var time = self.val();
		commonCountDown(time, self.next());
	});
}

function init() {
	$(".top-bar-flex .header-nav li").each(function () {
		var top = 0;
		var flag = $(this).attr("data-flag");
		switch (flag) {
			case "goods":
				break;
			case "evaluation" :
				top = $(".product-evaluation-main").offset().top - $(".product-evaluation-main").outerHeight();
				break;
			case "details":
				top = $(".product-details").offset().top;
				break
		}
		$(this).attr("data-top", top);
	});
}